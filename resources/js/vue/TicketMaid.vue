<script setup>
import { ref, computed } from 'vue'
import { useMutation } from '@tanstack/vue-query'
import RemoteSelect from '../components/RemoteSelect.vue'
import { usePaginationQuery } from '@/composables/usePagination'

// Toast notification state
const toast = ref({
  show: false,
  message: '',
  type: 'info' // 'success', 'warning', 'danger', 'info'
})

const showToast = (message, type = 'info') => {
  toast.value = { show: true, message, type }
  setTimeout(() => {
    toast.value.show = false
  }, 3000)
}

// Filters
const filters = {
  status: ref(''),
  nationality: ref(''),
}

const statusFilter = computed({
  get: () => filters.status.value,
  set: (val) => (filters.status.value = val)
})

const nationalityFilter = computed({
  get: () => filters.nationality.value,
  set: (val) => (filters.nationality.value = val)
})

// Pagination
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
  apiUrl: '/ticket-maid-list',
  queryKeyPrefix: 'ticket-maid-list',
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

// Dialog + Form State
const dialogVisible = ref(false)
const editingId = ref(null)
const form = ref({
  maid_name: '',
  travel_date: '',
  destination: '',
  return_date: '',
  status: 'pending',
  ticket_number: '',
  ticket_type: '',
  ticket_price: '',
  note: ''
})

// Country Suggestions
const countries = ref([])
const countrySearch = ref('')

async function searchCountries(query) {
  if (!query || query.length < 2) {
    countries.value = []
    return
  }

  try {
    const res = await axios.get(`https://restcountries.com/v3.1/name/${query}`)
    countries.value = res.data.map(c => c.name.common)
  } catch {
    countries.value = []
  }
}

function selectCountry(country) {
  form.value.destination = country
  countries.value = []
  countrySearch.value = ''
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
  
  if (query.length < 3) {
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
  // Extract clean name - if text has " / ", take only the part before it
  let cleanName = maid.name
  if (!cleanName && maid.text) {
    // If text is like "TEST MAID / UAE ID: xxx", extract just "TEST MAID"
    cleanName = maid.text.split(' / ')[0].trim()
  }
  
  form.value.maid_name = cleanName
  maidSearchInput.value = ''
  showMaidDropdown.value = false
  maidOptions.value = []
}

// Format Date
function formatDate(date) {
  if (!date) return null
  return new Date(date).toISOString().split('T')[0]
}

// Mutation (Create or Update)
const {
  isPending,
  mutate
} = useMutation({
  mutationFn: async (ticketData) => {
    const payload = {
      ...ticketData,
      travel_date: formatDate(ticketData.travel_date),
      return_date: formatDate(ticketData.return_date),
      ticket_price: ticketData.ticket_price ? String(ticketData.ticket_price) : ''
    }

    if (editingId.value) {
      return axios.put(`/ticket-maid/${editingId.value}`, payload)
    } else {
      return axios.post('/store-or-update-ticket', payload)
    }
  },
  onSuccess: (data) => {
    showToast(editingId.value ? 'Ticket updated successfully' : 'Ticket created successfully', 'success')
    dialogVisible.value = false
    editingId.value = null
    resetForm()
    refetch()
  },
  onError: (error) => {
    console.error('Full error:', error)
    console.error('Error response:', error.response)
    console.error('Error data:', error.response?.data)
    
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      console.error('Validation errors:', errors)
      showToast(Object.values(errors)[0][0], 'danger')
    } else if (error.response?.data?.message) {
      showToast(error.response.data.message, 'danger')
    } else {
      showToast('Something went wrong', 'danger')
    }
  }
})

// Handle Submit
const handleSubmit = () => {
  console.log('Form data before submit:', form.value)
  console.log('Travel date:', form.value.travel_date, typeof form.value.travel_date)
  console.log('Return date:', form.value.return_date, typeof form.value.return_date)
  mutate(form.value)
}

// Edit Form
const editForm = (row) => {
  form.value = {
    maid_name: row.maid_name,
    travel_date: row.travel_date || '', // Keep as string for HTML date input
    return_date: row.return_date || '', // Keep as string for HTML date input
    destination: row.destination,
    status: row.status,
    ticket_number: row.ticket_number,
    ticket_type: row.ticket_type,
    ticket_price: row.ticket_price,
    note: row.note
  }
  editingId.value = row.id
  dialogVisible.value = true
}

// Reset Form
const resetForm = () => {
  form.value = {
    maid_name: '',
    travel_date: '',
    destination: '',
    return_date: '',
    status: 'pending',
    ticket_number: '',
    ticket_type: '',
    ticket_price: '',
    note: ''
  }
  editingId.value = null
}
</script>

<template>
  <div class="ticket-container">
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

    <!-- Header with Primary Action -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <h2 class="h4 fw-bold mb-0 d-flex align-items-center">
        <i class="ki-duotone ki-airplane fs-1 me-2 text-primary">
          <i class="path1"></i>
          <i class="path2"></i>
        </i>
        Ticket Maid
      </h2>

      <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary d-flex align-items-center" @click="dialogVisible = true">
          <i class="ki-duotone ki-plus fs-2 me-2">
            <i class="path1"></i>
            <i class="path2"></i>
          </i>
          Add Ticket Maid
        </button>
        <button type="button" class="btn btn-success d-flex align-items-center" @click="exportToExcel">
          <i class="ki-duotone ki-file-down fs-2 me-2">
            <i class="path1"></i>
            <i class="path2"></i>
          </i>
          Export Excel
        </button>
      </div>
    </div>

    <!-- Filters Card -->
    <div class="card ticket-card mb-4 border-0 shadow-sm">
      <div class="card-body p-4">
        <div class="row g-4 align-items-end">
          <!-- Status Filter -->
          <div class="col-md-3">
            <label class="form-label fw-semibold text-muted small text-uppercase mb-1">Status</label>
            <div class="input-group">
              <span class="input-group-text border-end-0">
                <i class="ki-duotone ki-shield-tick fs-1">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
              </span>
              <select v-model="statusFilter" class="form-select border-start-0 ps-0">
                <option value="">All</option>
                <option value="rejected">Rejected</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
              </select>
            </div>
          </div>

          <!-- Nationality Filter -->
          <div class="col-md-3">
            <label class="form-label fw-semibold text-muted small text-uppercase mb-1">Nationality</label>
            <div class="input-group">
              <span class="input-group-text border-end-0">
                <i class="ki-duotone ki-flag fs-1">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
              </span>
              <select v-model="nationalityFilter" class="form-select border-start-0 ps-0">
                <option value="">All</option>
                <option value="Indonesia">Indonesia</option>
                <option value="Ethiopia">Ethiopia</option>
                <option value="Philippines">Philippines</option>
                <option value="Myanmar">Myanmar</option>
                <option value="Kenya">Kenya</option>
                <option value="Uganda">Uganda</option>
                <option value="Sri_Lanka">Sri Lanka</option>
                <option value="Tanzanian">Tanzanian</option>
                <option value="India">India</option>
                <option value="Ghana">Ghana</option>
                <option value="nepal">Nepal</option>
                <option value="pakistan">Pakistan</option>
                <option value="zimbabwe">Zimbabwe</option>
              </select>
            </div>
          </div>

          <!-- Search -->
          <div class="col-md-6">
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
                placeholder="Search maid name..."
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="card ticket-card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover ticket-table mb-0">
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
                  <i class="ki-duotone ki-flag fs-5 me-1 text-warning">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Nationality
                </th>
                <th>Agency</th>
                <th>Passport #</th>
                <th>
                  <i class="ki-duotone ki-calendar fs-5 me-1 text-info">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Travel Date
                </th>
                <th>
                  <i class="ki-duotone ki-geolocation fs-5 me-1 text-success">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Destination
                </th>
                <th>Return Date</th>
                <th>Status</th>
                <th>Ticket #</th>
                <th>Type</th>
                <th>Price</th>
                <th>Note</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Updated By</th>
                <th>Created By</th>
                <th style="width: 100px">Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Skeleton loader -->
              <template v-if="isLoading">
                <tr v-for="i in pageSize" :key="`skeleton-${i}`" class="skeleton-row">
                  <td><div class="skeleton skeleton-text" style="width: 70%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 60%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 65%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 75%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 70%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 65%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 70%"></div></td>
                  <td><div class="skeleton skeleton-badge" style="width: 70px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 60%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 55%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 50px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 80%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 70%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 70%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 60%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 60%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 60px"></div></td>
                </tr>
              </template>

              <!-- Empty state -->
              <tr v-else-if="!isLoading && data && data.data && !data.data.length">
                <td colspan="17" class="text-center text-muted py-4">
                  <i class="ki-duotone ki-inbox fs-1 d-block mb-2">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  No tickets found.
                </td>
              </tr>

              <!-- Actual data -->
              <tr v-else-if="data && data.data" v-for="row in data.data" :key="row.id">
                <td>
                  <a :href="`/page/maid-finance/${encodeURIComponent(row.maid_name)}`" class="text-decoration-none">
                    {{ row.maid_name }}
                  </a>
                </td>
                <td>{{ row.nationality }}</td>
                <td>{{ row.agency }}</td>
                <td>{{ row.passport_number }}</td>
                <td>{{ row.travel_date }}</td>
                <td>{{ row.destination }}</td>
                <td>{{ row.return_date }}</td>
                <td>
                  <span class="badge" :class="{
                    'bg-success': row.status === 'approved',
                    'bg-warning text-dark': row.status === 'pending',
                    'bg-danger': row.status === 'rejected'
                  }">
                    {{ row.status }}
                  </span>
                </td>
                <td>{{ row.ticket_number }}</td>
                <td>{{ row.ticket_type }}</td>
                <td>{{ row.ticket_price }}</td>
                <td>{{ row.note }}</td>
                <td>{{ row.created_at }}</td>
                <td>{{ row.updated_at }}</td>
                <td>{{ row.updated_by }}</td>
                <td>{{ row.created_by }}</td>
                <td>
                  <button type="button" class="btn btn-sm btn-primary" @click="editForm(row)">
                    <i class="ki-duotone ki-notepad-edit fs-4">
                      <i class="path1"></i>
                      <i class="path2"></i>
                    </i>
                    Edit
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination Footer -->
      <div v-if="isLoading || (data && total > 0)" class="card-footer ticket-footer">
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
        <div class="modal-content ticket-modal">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">
              <i class="ki-duotone ki-airplane fs-1 me-2 text-primary">
                <i class="path1"></i>
                <i class="path2"></i>
              </i>
              {{ editingId ? 'Edit Ticket Maid' : 'Add Ticket Maid' }}
            </h5>
            <button type="button" class="btn-close" @click="dialogVisible = false; resetForm()"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="handleSubmit">
              <div class="row g-4">
                <!-- Maid Name -->
                <div class="col-12">
                  <label class="form-label fw-semibold">Maid Name</label>
                  <div class="position-relative">
                    <input
                      v-model="maidSearchInput"
                      type="text"
                      class="form-control"
                      placeholder="Type to search maid (min 3 chars)..."
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

                <!-- Travel Date -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Travel Date</label>
                  <input v-model="form.travel_date" type="date" class="form-control" />
                </div>

                <!-- Return Date -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Return Date</label>
                  <input v-model="form.return_date" type="date" class="form-control" />
                </div>

                <!-- Destination with Autocomplete -->
                <div class="col-12">
                  <label class="form-label fw-semibold">Destination</label>
                  <div class="position-relative">
                    <input
                      v-model="form.destination"
                      type="text"
                      class="form-control"
                      placeholder="Search country..."
                      @input="searchCountries(form.destination)"
                    />
                    <div v-if="countries.length > 0" class="dropdown-menu show w-100 mt-1" style="max-height: 200px; overflow-y: auto;">
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

                <!-- Status -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Status</label>
                  <select v-model="form.status" class="form-select">
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                  </select>
                </div>

                <!-- Ticket Number -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Ticket Number</label>
                  <input v-model="form.ticket_number" type="text" class="form-control" />
                </div>

                <!-- Ticket Type -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Ticket Type</label>
                  <input v-model="form.ticket_type" type="text" class="form-control" />
                </div>

                <!-- Ticket Price -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Ticket Price</label>
                  <input v-model="form.ticket_price" type="number" class="form-control" step="0.01" />
                </div>

                <!-- Note -->
                <div class="col-12">
                  <label class="form-label fw-semibold">Note</label>
                  <textarea v-model="form.note" class="form-control" rows="3"></textarea>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="dialogVisible = false; resetForm()">Cancel</button>
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
.ticket-container {
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  margin: 1.5rem 3rem;
}

.ticket-card,
.ticket-modal {
  background-color: var(--bs-card-bg);
  border-color: var(--bs-border-color);
}

.ticket-table {
  color: var(--bs-body-color);
}

.ticket-table thead {
  background-color: var(--bs-body-bg);
}

.ticket-table thead th {
  padding: 1rem;
  font-weight: 600;
  color: var(--bs-emphasis-color);
  white-space: nowrap;
  font-size: 0.85rem;
  border-bottom: 2px solid var(--bs-border-color);
}

.ticket-table th:first-child,
.ticket-table td:first-child {
  padding-left: 1.25rem;
}

.ticket-table tbody td {
  padding: 0.75rem 1rem;
  vertical-align: middle;
  border-bottom: 1px solid var(--bs-border-color);
}

.ticket-table tbody tr:hover {
  background-color: var(--bs-tertiary-bg);
}

.ticket-footer {
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

/* Dropdown */
.dropdown-menu {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
}

.dropdown-item {
  color: var(--bs-body-color);
}

.dropdown-item:hover {
  background-color: var(--bs-tertiary-bg);
  color: var(--bs-body-color);
}

/* Badge adjustments */
.badge {
  font-weight: 600;
  padding: 0.35rem 0.65rem;
  font-size: 0.75rem;
}

/* Link hover effects */
a.text-primary:hover {
  text-decoration: underline !important;
}

/* Custom scrollbar for table */
.table-responsive::-webkit-scrollbar {
  height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
  background: var(--bs-secondary-bg);
}

.table-responsive::-webkit-scrollbar-thumb {
  background: var(--bs-border-color);
  border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
  background: var(--bs-secondary-color);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .ticket-container {
    margin: 1rem;
  }

  .ticket-table thead th,
  .ticket-table tbody td {
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
  height: 1.63rem;
}

.skeleton-text {
  height: 1.63rem;
}

.skeleton-badge {
  height: 1.63rem;
  border-radius: 0.4rem;
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
</style>
```
