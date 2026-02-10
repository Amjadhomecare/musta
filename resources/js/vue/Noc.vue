<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { useMutation } from '@tanstack/vue-query'
import { usePaginationQuery } from '@/composables/usePagination'

// ── Pagination ────────────────────────────────────────────────────────────────
const {
  data,
  isLoading,
  refetch,
  currentPage,
  pageSize,
  searchQuery,
  total,
  handlePageChange,
  handleSizeChange,
  exportToExcel
} = usePaginationQuery({
  apiUrl: '/noc-list',
  queryKeyPrefix: 'noc-maid-list'
})

// Smart pagination
const totalPages = computed(() => {
  if (!total.value || !pageSize.value) return 1
  return Math.ceil(total.value / pageSize.value)
})

const visiblePages = computed(() => {
  const current = currentPage.value
  const totalPagesValue = totalPages.value
  const maxVisible = 5

  if (totalPagesValue <= maxVisible + 2) {
    return Array.from({ length: totalPagesValue }, (_, i) => i + 1)
  }

  const halfVisible = Math.floor(maxVisible / 2)
  let start = Math.max(1, current - halfVisible)
  let end = Math.min(totalPagesValue, current + halfVisible)

  if (current <= halfVisible + 1) {
    start = 1
    end = maxVisible
  }

  if (current >= totalPagesValue - halfVisible) {
    start = totalPagesValue - maxVisible + 1
    end = totalPagesValue
  }

  return Array.from({ length: end - start + 1 }, (_, i) => start + i)
})

// ── Toast Notifications ───────────────────────────────────────────────────────
const toast = ref({
  show: false,
  message: '',
  type: 'info'
})

const showToast = (message, type = 'info') => {
  toast.value = { show: true, message, type }
  setTimeout(() => {
    toast.value.show = false
  }, 3000)
}

// ── Dialog / Form state ───────────────────────────────────────────────────────
const dialogVisible = ref(false)
const editingId = ref(null)
const form = ref({
  id: null,
  maid_name: '',      // required by backend
  maid_extra: {},     // full maid object -> will go to extra_data
  customer_name: '',
  note: '',
  t_date: null,
  r_date: null,
  country: '',
  cus_phone: '',
  cus_id: ''
})

// ── Helpers ───────────────────────────────────────────────────────────────────
const formatDate = d => (d ? new Date(d).toISOString().split('T')[0] : null)

// Country autocomplete
const countries = ref([])
const countrySearch = ref('')
const showCountryDropdown = ref(false)

async function searchCountries(query) {
  if (!query || query.length < 2) {
    countries.value = []
    return
  }

  try {
    const res = await axios.get(`https://restcountries.com/v3.1/name/${query}`)
    countries.value = res.data.map(c => c.name.common)
    showCountryDropdown.value = true
  } catch {
    countries.value = []
    showCountryDropdown.value = false
  }
}

function selectCountry(country) {
  form.value.country = country
  countrySearch.value = ''
  showCountryDropdown.value = false
}

// Maid Search
const maidSearchInput = ref('')
const maidOptions = ref([])
const showMaidDropdown = ref(false)
const loadingMaids = ref(false)
let maidSearchTimer = null

async function handleMaidSearch() {
  const query = maidSearchInput.value
  
  if (maidSearchTimer) clearTimeout(maidSearchTimer)
  
  if (query.length < 2) {
    showMaidDropdown.value = false
    maidOptions.value = []
    return
  }
  
  maidSearchTimer = setTimeout(async () => {
    loadingMaids.value = true
    showMaidDropdown.value = true
    
    try {
      const response = await axios.get('https://api.alahliamaids.com/MaidsSearch', {
        params: { query: query }
      })
      maidOptions.value = Array.isArray(response.data) ? response.data : []
    } catch (error) {
      console.error('Error fetching maids:', error)
      maidOptions.value = []
    } finally {
      loadingMaids.value = false
    }
  }, 300)
}

function selectMaid(maid) {
  form.value.maid_name = maid.text || maid.name
  form.value.maid_extra = maid
  
  // Auto-fill country if available
  if (!form.value.country && maid.nationality) {
    form.value.country = maid.nationality
  }
  
  maidSearchInput.value = ''
  showMaidDropdown.value = false
  maidOptions.value = []
}

// ── Create / Update mutation ──────────────────────────────────────────────────
const { mutate, isPending } = useMutation({
  mutationFn: async payload => {
    const dataToSend = {
      ...payload,
      t_date: payload.t_date, // Already string from HTML date input
      r_date: payload.r_date, // Already string from HTML date input
      // Send extra_data as: { cus_phone, cus_id } + maid_extra from selector
      extra_data: {
        ...(payload.maid_extra || {}),
        // keep your custom extras together here:
        cus_phone: payload.cus_phone || null,
        cus_id: payload.cus_id || null
      }
    }
    // Do not also send cus_phone/cus_id at the root, the controller merges them from extra_data
    delete dataToSend.maid_extra

    return editingId.value
      ? axios.put(`/store-noc/${editingId.value}`, dataToSend)
      : axios.post('/store-noc', dataToSend)
  },
  onSuccess() {
    showToast(editingId.value ? 'NOC updated' : 'NOC created', 'success')
    resetForm()
    dialogVisible.value = false
    refetch()
  },
  onError(err) {
    const first = err?.response?.data?.errors
      ? Object.values(err.response.data.errors)[0][0]
      : 'Something went wrong'
    showToast(first, 'danger')
  }
})

// ── Actions ───────────────────────────────────────────────────────────────────
const handleSubmit = () => {
  // Minimal safeguard: ensure maid_name is set
  if (!form.value.maid_name) {
    showToast('Please select a maid', 'warning')
    return
  }
  mutate({ ...form.value })
}

function editForm(row) {
  // assumes backend includes extra_data on rows; fallback to {}
  const extra = row.extra_data || {}
  form.value = {
    id: row.id,
    maid_name: row.maid_name || '',
    maid_extra: extra,
    customer_name: row.customer_name || '',
    note: row.note || '',
    t_date: row.t_date || '',
    r_date: row.r_date || '',
    country: row.country || '',
    cus_phone: (extra.cus_phone ?? row.cus_phone) || '',
    cus_id: (extra.cus_id ?? row.cus_id) || ''
  }
  editingId.value = row.id
  dialogVisible.value = true
}

function resetForm() {
  form.value = {
    id: null,
    maid_name: '',
    maid_extra: {},
    customer_name: '',
    note: '',
    t_date: null,
    r_date: null,
    country: '',
    cus_phone: '',
    cus_id: ''
  }
  editingId.value = null
}
</script>

<template>
  <div class="noc-container">
    <!-- Toast Notifications -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
      <div v-for="(t, index) in [toast].filter(t => t.show)" :key="index" 
           class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header" :class="{
          'bg-success text-white': t.type === 'success',
          'bg-warning text-dark': t.type === 'warning',
          'bg-danger text-white': t.type === 'danger',
          'bg-info text-white': t.type === 'info'
        }">
          <i class="ki-duotone me-2" :class="{
            'ki-check-circle': t.type === 'success',
            'ki-information': t.type === 'warning' || t.type === 'info',
            'ki-cross-circle': t.type === 'danger'
          }">
            <i class="path1"></i>
            <i class="path2"></i>
          </i>
          <strong class="me-auto">{{ t.type.charAt(0).toUpperCase() + t.type.slice(1) }}</strong>
          <button type="button" class="btn-close btn-close-white" @click="t.show = false" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          {{ t.message }}
        </div>
      </div>
    </div>

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <h2 class="h4 fw-bold mb-0 d-flex align-items-center">
        <i class="ki-duotone ki-document fs-1 me-2 text-primary">
          <i class="path1"></i>
          <i class="path2"></i>
        </i>
        NOC Maid Management
      </h2>
    </div>

    <!-- Controls Toolbar -->
    <div class="card noc-card mb-4 border-0 shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row align-items-md-end gap-3">
          <!-- Search -->
          <div class="flex-grow-1">
            <label class="form-label fw-semibold text-muted small text-uppercase mb-1">Search</label>
            <div class="input-group">
              <span class="input-group-text border-end-0">
                <i class="ki-duotone ki-magnifier fs-1">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
              </span>
              <input
                v-model="searchQuery"
                type="text"
                class="form-control border-start-0 ps-0"
                placeholder="Search maid name…"
                @input="refetch"
              />
            </div>
          </div>

          <!-- Actions -->
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary d-flex align-items-center" @click="dialogVisible = true">
              <i class="ki-duotone ki-plus fs-2 me-2">
                <i class="path1"></i>
                <i class="path2"></i>
              </i>
              Add NOC Maid
            </button>
            
            <button type="button" class="btn btn-success d-flex align-items-center" @click="exportToExcel">
              <i class="ki-duotone ki-file-down fs-2 me-2">
                <i class="path1"></i>
                <i class="path2"></i>
              </i>
              Export
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="card noc-card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover noc-table mb-0">
            <thead>
              <tr>
                <th>
                  <i class="ki-duotone ki-user fs-5 me-1 text-primary">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Maid Name
                </th>
                <th>
                  <i class="ki-duotone ki-profile-user fs-5 me-1 text-success">
                    <i class="path1"></i>
                    <i class="path2"></i>
                    <i class="path3"></i>
                    <i class="path4"></i>
                  </i>
                  Customer Name
                </th>
                <th>
                  <i class="ki-duotone ki-map fs-5 me-1 text-info">
                    <i class="path1"></i>
                    <i class="path2"></i>
                    <i class="path3"></i>
                  </i>
                  Destination
                </th>
                <th>
                  <i class="ki-duotone ki-calendar fs-5 me-1 text-warning">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Travel Date
                </th>
                <th>
                  <i class="ki-duotone ki-calendar-tick fs-5 me-1 text-danger">
                    <i class="path1"></i>
                    <i class="path2"></i>
                    <i class="path3"></i>
                  </i>
                  Return Date
                </th>
                <th style="width: 160px">Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Skeleton loader -->
              <template v-if="isLoading">
                <tr v-for="i in pageSize" :key="`skeleton-${i}`" class="skeleton-row">
                  <td><div class="skeleton skeleton-text" style="width: 70%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 70%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 60%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 80px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 80px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 100px"></div></td>
                </tr>
              </template>

              <!-- Empty state -->
              <tr v-else-if="!isLoading && data && data.data && !data.data.length">
                <td colspan="6" class="text-center text-muted py-4">
                  <i class="ki-duotone ki-inbox fs-1 d-block mb-2">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  No records found.
                </td>
              </tr>

              <!-- Actual data -->
              <tr v-else-if="data && data.data" v-for="row in data.data" :key="row.id" @dblclick="editForm(row)" class="cursor-pointer">
                <td>
                  <a :href="`/page/maid-finance/${encodeURIComponent(row.maid_name)}`" class="text-primary text-decoration-none hover-underline" @click.stop>
                    {{ row.maid_name }}
                  </a>
                </td>
                <td>{{ row.customer_name }}</td>
                <td>{{ row.country }}</td>
                <td>{{ row.t_date }}</td>
                <td>{{ row.r_date }}</td>
                <td>
                  <div class="d-flex gap-2">
                    <a :href="`/get-noc-by-id/${encodeURIComponent(row.id)}`" class="btn btn-sm btn-info text-white" @click.stop>
                      View
                    </a>
                    <button type="button" class="btn btn-sm btn-primary" @click.stop="editForm(row)">
                      Edit
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination Footer -->
      <div v-if="isLoading || (data && total > 0)" class="card-footer noc-footer">
        <nav class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <!-- Pagination controls -->
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <a class="page-link" href="#" @click.prevent="currentPage > 1 && handlePageChange(currentPage - 1)">
                <i class="ki-duotone ki-left fs-5">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
                Previous
              </a>
            </li>

            <li v-if="visiblePages[0] > 1" class="page-item">
              <a class="page-link" href="#" @click.prevent="handlePageChange(1)">1</a>
            </li>

            <li v-if="visiblePages[0] > 2" class="page-item disabled">
              <span class="page-link">...</span>
            </li>

            <li v-for="pageNum in visiblePages" :key="pageNum" class="page-item" :class="{ active: pageNum === currentPage }">
              <a class="page-link" href="#" @click.prevent="handlePageChange(pageNum)">{{ pageNum }}</a>
            </li>

            <li v-if="visiblePages[visiblePages.length - 1] < totalPages - 1" class="page-item disabled">
              <span class="page-link">...</span>
            </li>

            <li v-if="visiblePages[visiblePages.length - 1] < totalPages" class="page-item">
              <a class="page-link" href="#" @click.prevent="handlePageChange(totalPages)">{{ totalPages }}</a>
            </li>

            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <a class="page-link" href="#" @click.prevent="currentPage < totalPages && handlePageChange(currentPage + 1)">
                Next
                <i class="ki-duotone ki-right fs-5">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
              </a>
            </li>
          </ul>

          <!-- Page info -->
          <div class="text-muted small">
            <i class="ki-duotone ki-element-11 fs-5 me-1">
              <i class="path1"></i>
              <i class="path2"></i>
              <i class="path3"></i>
              <i class="path4"></i>
            </i>
            Total Results: <strong class="text-primary">{{ total || 0 }}</strong>
          </div>
        </nav>
      </div>
    </div>

    <!-- Modal Dialog -->
    <div v-if="dialogVisible" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content noc-modal">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">
              <i class="ki-duotone ki-document fs-1 me-2 text-primary">
                <i class="path1"></i>
                <i class="path2"></i>
              </i>
              {{ editingId ? 'Edit NOC Maid' : 'Add NOC Maid' }}
            </h5>
            <button type="button" class="btn-close" @click="dialogVisible = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="handleSubmit">
              <div class="row g-3">
                <!-- Maid Selection -->
                <div class="col-12">
                  <label class="form-label fw-semibold">Maid Name</label>
                  <div class="position-relative">
                    <input
                      v-model="maidSearchInput"
                      type="text"
                      class="form-control"
                      placeholder="Type to search maid (min 2 chars)..."
                      @input="handleMaidSearch"
                      autocomplete="off"
                    />
                    
                    <!-- Dropdown -->
                    <div v-if="showMaidDropdown && (maidOptions.length || loadingMaids)" 
                         class="dropdown-menu show w-100 mt-1 shadow" 
                         style="max-height: 250px; overflow-y: auto; position: absolute; z-index: 1050;">
                      
                      <div v-if="loadingMaids" class="dropdown-item-text text-center py-3">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Loading...
                      </div>
                      
                      <button
                        v-else
                        v-for="maid in maidOptions"
                        :key="maid.id"
                        type="button"
                        class="dropdown-item"
                        @click="selectMaid(maid)"
                      >
                        {{ maid.text || maid.name }}
                      </button>
                      
                      <div v-if="!loadingMaids && maidOptions.length === 0" class="dropdown-item-text text-center text-muted py-3">
                        No results found
                      </div>
                    </div>
                  </div>
                  
                  <!-- Selected display -->
                  <div v-if="form.maid_name" class="mt-2">
                    <span class="badge bg-primary">
                      <i class="ki-duotone ki-check me-1">
                        <i class="path1"></i>
                        <i class="path2"></i>
                      </i>
                      {{ form.maid_name }}
                    </span>
                  </div>
                </div>

                <!-- Read-only fields -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Passport</label>
                  <input type="text" class="form-control bg-light" :value="form.maid_extra?.passport || '—'" readonly />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Nationality</label>
                  <input type="text" class="form-control bg-light" :value="form.maid_extra?.nationality || '—'" readonly />
                </div>

                <!-- Customer Details -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Customer Name</label>
                  <input v-model="form.customer_name" type="text" class="form-control" placeholder="Enter customer name" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Customer Phone</label>
                  <input v-model="form.cus_phone" type="text" class="form-control" placeholder="Enter customer phone" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Customer ID</label>
                  <input v-model="form.cus_id" type="text" class="form-control" placeholder="Enter customer ID" />
                </div>

                <!-- Travel Details -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Destination (Country)</label>
                  <div class="position-relative">
                    <input
                      v-model="form.country"
                      type="text"
                      class="form-control"
                      placeholder="Search country..."
                      @input="e => searchCountries(e.target.value)"
                      autocomplete="off"
                    />
                    <div v-if="showCountryDropdown && countries.length" 
                         class="dropdown-menu show w-100 mt-1 shadow" 
                         style="max-height: 200px; overflow-y: auto; position: absolute; z-index: 1050;">
                      <button
                        v-for="country in countries"
                        :key="country"
                        type="button"
                        class="dropdown-item"
                        @click="selectCountry(country)"
                      >
                        {{ country }}
                      </button>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-semibold">Travel Date</label>
                  <input v-model="form.t_date" type="date" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Return Date</label>
                  <input v-model="form.r_date" type="date" class="form-control" />
                </div>

                <div class="col-12">
                  <label class="form-label fw-semibold">Note</label>
                  <textarea v-model="form.note" class="form-control" rows="2"></textarea>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="dialogVisible = false">Cancel</button>
            <button type="button" class="btn btn-primary" :disabled="isPending" @click="handleSubmit">
              <span v-if="isPending" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              {{ editingId ? 'Update' : 'Submit' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Theme-adaptive styling using Bootstrap CSS variables */
.noc-container {
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  margin: 1.5rem 3rem;
}

.noc-card,
.noc-modal {
  background-color: var(--bs-card-bg);
  border-color: var(--bs-border-color);
}

.noc-table {
  color: var(--bs-body-color);
}

.noc-table thead {
  background-color: var(--bs-body-bg);
}

.noc-table thead th {
  padding: 1rem;
  font-weight: 600;
  color: var(--bs-emphasis-color);
  white-space: nowrap;
  font-size: 0.85rem;
  border-bottom: 2px solid var(--bs-border-color);
}

.noc-table th:first-child,
.noc-table td:first-child {
  padding-left: 1.25rem;
}

.noc-table tbody td {
  padding: 0.75rem 1rem;
  vertical-align: middle;
  border-bottom: 1px solid var(--bs-border-color);
}

.noc-table tbody tr:hover {
  background-color: var(--bs-tertiary-bg);
}

.noc-footer {
  background-color: var(--bs-body-bg);
  border-top: 1px solid var(--bs-border-color);
  padding: 1rem 1.5rem;
}

/* Form controls */
.form-control {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
  color: var(--bs-body-color);
}

.form-control:focus {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-primary);
  color: var(--bs-body-color);
}

.input-group-text {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
  color: var(--bs-body-color);
}

/* Darker background for icon containers in dark mode */
[data-bs-theme="dark"] .input-group-text {
  background-color: #0d0d14;
}

/* Modal */
.modal-content {
  background-color: var(--bs-body-bg) !important;
  border-color: var(--bs-border-color);
}

.modal-header {
  background-color: var(--bs-body-bg);
  border-bottom: 1px solid var(--bs-border-color);
}

.modal-body {
  background-color: var(--bs-body-bg) !important;
}

.modal-footer {
  background-color: var(--bs-body-bg);
  border-top: 1px solid var(--bs-border-color);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .noc-container {
    margin: 1rem;
  }

  .noc-table thead th,
  .noc-table tbody td {
    padding: 0.5rem;
    font-size: 0.85rem;
  }
}

/* Skeleton Loader Styles */
.skeleton {
  background: linear-gradient(
    90deg,
    var(--bs-tertiary-bg) 0%,
    var(--bs-secondary-bg) 50%,
    var(--bs-tertiary-bg) 100%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 0.25rem;
  height: 1.25rem;
}

.skeleton-text {
  height: 1.25rem;
}

.skeleton-row {
  opacity: 0.7;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

.hover-underline:hover {
  text-decoration: underline !important;
}

.cursor-pointer {
  cursor: pointer;
}
</style>
