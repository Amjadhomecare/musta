<script setup>
import { ref, computed } from 'vue'
import { useMutation } from '@tanstack/vue-query'
import { usePaginationQuery } from '@/composables/usePagination'
import axios from 'axios'

// Filters
const filters = {
  reason: ref('')
}

const reasonFilter = computed({
  get: () => filters.reason.value,
  set: (val) => (filters.reason.value = val)
})

// Pagination Composable
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
  apiUrl: '/leave-salaries',
  queryKeyPrefix: 'leave-salaries',
  filters
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

// Toast Notifications
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

// Dialog, Form, State
const dialogVisible = ref(false)
const editingId = ref(null)
const modalLoading = ref(false)

const form = ref({
  maid_name: '',
  last_entry_date: '',
  travel_date: '',
  dedcution: null,
  ticket: null,
  allowance: 2000,
  note: '',
  reason: '',
  remaining_amount: 0,
  for: 'maid',
  salary_dh: 0,
  salary_details: '14 days',
  end_of_service_dh: 0,
  end_of_service_details: '21 days for each YEAR',
  other_dh: 0,
  other_details: '-'
})

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
      const response = await axios.get('/all/maids', {
        params: { search: query }
      })
      maidOptions.value = response.data.items || []
    } catch (error) {
      console.error('Error fetching maids:', error)
      maidOptions.value = []
    } finally {
      loadingMaids.value = false
    }
  }, 300)
}

function selectMaid(maid) {
  // Use maid.id which contains the clean name
  form.value.maid_name = maid.id
  maidSearchInput.value = ''
  showMaidDropdown.value = false
  maidOptions.value = []
}

// Mutation (Create/Update)
const { mutate, isPending } = useMutation({
  mutationFn: async (formData) => {
    const payload = {
      ...formData,
      last_entry_date: formData.last_entry_date || null,
      travel_date: formData.travel_date || null
    }

    if (editingId.value) {
      return axios.put(`/leave-salaries/${editingId.value}`, payload)
    } else {
      return axios.post('/store-leave-salary', payload)
    }
  },
  onSuccess: () => {
    showToast(editingId.value ? 'Leave salary updated successfully' : 'Leave salary added successfully', 'success')
    dialogVisible.value = false
    resetForm()
    refetch()
  },
  onError: (error) => {
    showToast(error.response?.data?.message || 'Failed to submit', 'danger')
  }
})

const submitForm = () => {
  if (!form.value.maid_name) {
    showToast('Maid name is required', 'warning')
    return
  }
  mutate(form.value)
}

const resetForm = () => {
  form.value = {
    maid_name: '',
    last_entry_date: '',
    travel_date: '',
    dedcution: null,
    ticket: null,
    allowance: 2000,
    note: '',
    reason: '',
    remaining_amount: 0,
    for: 'maid',
    salary_dh: 0,
    salary_details: '14 days',
    end_of_service_dh: 0,
    end_of_service_details: '21 days for each YEAR',
    other_dh: 0,
    other_details: '-'
  }
  editingId.value = null
}

const editForm = (row) => {
  form.value = {
    maid_name: row.maid_name,
    last_entry_date: row.last_entry_date ? new Date(row.last_entry_date).toISOString().split('T')[0] : '',
    travel_date: row.travel_date ? new Date(row.travel_date).toISOString().split('T')[0] : '',
    dedcution: row.dedcution,
    ticket: row.ticket,
    allowance: row.allowance,
    remaining_amount: row.remaining_amount,
    note: row.note,
    reason: row.reason,
    for: row.for || 'maid',
    salary_dh: row.salary_dh || 0,
    salary_details: row.salary_details || '14 days',
    end_of_service_dh: row.end_of_service_dh || 0,
    end_of_service_details: row.end_of_service_details || '21 days for each YEAR',
    other_dh: row.other_dh || 0,
    other_details: row.other_details || '-'
  }
  editingId.value = row.id
  dialogVisible.value = true
}
</script>

<template>
  <div class="leave-container">
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
        <i class="ki-duotone ki-calendar-tick fs-1 me-2 text-primary">
          <i class="path1"></i>
          <i class="path2"></i>
          <i class="path3"></i>
        </i>
        Leave Salary Records
      </h2>
    </div>

    <!-- Controls Toolbar -->
    <div class="card leave-card mb-4 border-0 shadow-sm">
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

          <!-- Filter -->
          <div>
            <label class="form-label fw-semibold text-muted small text-uppercase mb-1">Reason</label>
            <select v-model="reasonFilter" class="form-select">
              <option value="">All Reasons</option>
              <option value="cancel">Cancel</option>
              <option value="renewal">Renewal</option>
            </select>
          </div>

          <!-- Actions -->
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary d-flex align-items-center" @click="dialogVisible = true">
              <i class="ki-duotone ki-plus fs-2 me-2">
                <i class="path1"></i>
                <i class="path2"></i>
              </i>
              Add New
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
    <div class="card leave-card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover leave-table mb-0">
            <thead>
              <tr>
                <th>
                  <i class="ki-duotone ki-user fs-5 me-1 text-primary">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Maid Name
                </th>
                <th>Last Entry Date</th>
                <th>Travel Date</th>
                <th>Deduction</th>
                <th>Ticket</th>
                <th>Allowance</th>
                <th>Balance</th>
                <th>Note</th>
                <th>View</th>
                <th style="width: 100px">Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Skeleton loader -->
              <template v-if="isLoading">
                <tr v-for="i in pageSize" :key="`skeleton-${i}`" class="skeleton-row">
                  <td><div class="skeleton skeleton-text" style="width: 70%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 80px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 80px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 60px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 60px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 60px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 60px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 100px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 40px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 60px"></div></td>
                </tr>
              </template>

              <!-- Empty state -->
              <tr v-else-if="!isLoading && data && data.data && !data.data.length">
                <td colspan="10" class="text-center text-muted py-4">
                  <i class="ki-duotone ki-inbox fs-1 d-block mb-2">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  No records found.
                </td>
              </tr>

              <!-- Actual data -->
              <tr v-else-if="data && data.data" v-for="row in data.data" :key="row.id">
                <td>
                  <a :href="`/page/maid-finance/${encodeURIComponent(row.maid_name)}`" class="text-primary text-decoration-none hover-underline">
                    {{ row.maid_name }}
                  </a>
                </td>
                <td>{{ row.last_entry_date }}</td>
                <td>{{ row.travel_date }}</td>
                <td>{{ row.dedcution }}</td>
                <td>{{ row.ticket }}</td>
                <td>{{ row.allowance }}</td>
                <td>{{ row.remaining_amount }}</td>
                <td>{{ row.note }}</td>
                <td>
                  <a :href="`/maid-clearence/${row.id}`" target="_blank" class="btn btn-sm btn-icon btn-light-primary">
                    <i class="ki-duotone ki-eye fs-4">
                      <i class="path1"></i>
                      <i class="path2"></i>
                      <i class="path3"></i>
                    </i>
                  </a>
                </td>
                <td>
                  <button type="button" class="btn btn-sm btn-primary" @click="editForm(row)">
                    Edit
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination Footer -->
      <div v-if="isLoading || (data && total > 0)" class="card-footer leave-footer">
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
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content leave-modal">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">
              <i class="ki-duotone ki-calendar-tick fs-1 me-2 text-primary">
                <i class="path1"></i>
                <i class="path2"></i>
                <i class="path3"></i>
              </i>
              {{ editingId ? 'Edit Leave Salary' : 'Add Leave Salary' }}
            </h5>
            <button type="button" class="btn-close" @click="dialogVisible = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div class="mb-3">
                <label class="form-label fw-semibold">Select Maid</label>
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

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Allowance</label>
                  <input v-model="form.allowance" type="number" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Salary (DH)</label>
                  <input v-model="form.salary_dh" type="number" step="0.01" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Salary Details</label>
                  <input v-model="form.salary_details" type="text" class="form-control" placeholder="e.g. 14 days" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">End of Service (DH)</label>
                  <input v-model="form.end_of_service_dh" type="number" step="0.01" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">End of Service Details</label>
                  <input v-model="form.end_of_service_details" type="text" class="form-control" placeholder="e.g. 21 days for each YEAR" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Other (DH)</label>
                  <input v-model="form.other_dh" type="number" step="0.01" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Other Details</label>
                  <input v-model="form.other_details" type="text" class="form-control" placeholder="e.g. -" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Reason</label>
                  <select v-model="form.reason" class="form-select">
                    <option value="renewal">Renewal</option>
                    <option value="cancel">Cancel</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Balance</label>
                  <input v-model="form.remaining_amount" type="number" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Last Entry Date</label>
                  <input v-model="form.last_entry_date" type="date" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Travel Date</label>
                  <input v-model="form.travel_date" type="date" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Deduction</label>
                  <input v-model="form.dedcution" type="number" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Ticket</label>
                  <input v-model="form.ticket" type="number" class="form-control" />
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
            <button type="button" class="btn btn-primary" :disabled="isPending" @click="submitForm">
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
.leave-container {
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  margin: 1.5rem 3rem;
}

.leave-card,
.leave-modal {
  background-color: var(--bs-card-bg);
  border-color: var(--bs-border-color);
}

.leave-table {
  color: var(--bs-body-color);
}

.leave-table thead {
  background-color: var(--bs-body-bg);
}

.leave-table thead th {
  padding: 1rem;
  font-weight: 600;
  color: var(--bs-emphasis-color);
  white-space: nowrap;
  font-size: 0.85rem;
  border-bottom: 2px solid var(--bs-border-color);
}

.leave-table th:first-child,
.leave-table td:first-child {
  padding-left: 1.25rem;
}

.leave-table tbody td {
  padding: 0.75rem 1rem;
  vertical-align: middle;
  border-bottom: 1px solid var(--bs-border-color);
}

.leave-table tbody tr:hover {
  background-color: var(--bs-tertiary-bg);
}

.leave-footer {
  background-color: var(--bs-body-bg);
  border-top: 1px solid var(--bs-border-color);
  padding: 1rem 1.5rem;
}

/* Form controls */
.form-control,
.form-select {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
  color: var(--bs-body-color);
}

.form-control:focus,
.form-select:focus {
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
  .leave-container {
    margin: 1rem;
  }

  .leave-table thead th,
  .leave-table tbody td {
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
</style>
