// resource/js/vue/MaidsVistVisa.vue
<script setup>
import { ref, computed } from 'vue'
import { usePaginationQuery } from '@/composables/usePagination'

const selectedRows = ref([])
const tableRef = ref(null)

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

const handleSelectionChange = (maidId) => {
  const index = selectedRows.value.indexOf(maidId)
  if (index > -1) {
    selectedRows.value.splice(index, 1)
  } else {
    selectedRows.value.push(maidId)
  }
}

const toggleSelectAll = (event) => {
  if (event.target.checked) {
    selectedRows.value = data.value.data.map(row => row.id)
  } else {
    selectedRows.value = []
  }
}

const isSelected = (maidId) => {
  return selectedRows.value.includes(maidId)
}

const allSelected = computed(() => {
  return data.value?.data?.length > 0 && 
         selectedRows.value.length === data.value.data.length
})

const handleBulkAction = async () => {
  const ids = selectedRows.value

  if (ids.length === 0) {
    showToast('No maids selected.', 'warning')
    return
  }

  try {
    const response = await axios.put('/bulk-update-maid-visit-visa', {
      ids,
      visa_status: 'c'  
    })

    showToast(response.data.message || 'Updated successfully', 'success')
    refetch()
    selectedRows.value = []
  } catch (error) {
    showToast(
      error.response?.data?.error || 'Failed to update maid visa status',
      'danger'
    )
  }
}

// Filters
const filters = {
  remove_null: ref(''),
  search_nationality: ref('')
}

const showWithExpiryOnly = computed({
  get: () => filters.remove_null.value === '1',
  set: (val) => {
    filters.remove_null.value = val ? '1' : ''
  }
})

const searchNationality = computed({
  get: () => filters.search_nationality.value,
  set: (val) => {
    filters.search_nationality.value = val
  }
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
  apiUrl: '/maid-visit-visa',
  queryKeyPrefix: 'maids-visit-visa',
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

</script>


<template>
  <div class="visa-container p-4 rounded shadow-sm">
    <!-- Toast Notification -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
      <div
        v-if="toast.show"
        class="toast show"
        :class="`bg-${toast.type} text-white`"
        role="alert"
      >
        <div class="toast-body d-flex align-items-center">
          <i
            class="me-2"
            :class="{
              'ri-checkbox-circle-line': toast.type === 'success',
              'ri-error-warning-line': toast.type === 'warning',
              'ri-close-circle-line': toast.type === 'danger',
              'ri-information-line': toast.type === 'info'
            }"
          ></i>
          {{ toast.message }}
        </div>
      </div>
    </div>

    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <h2 class="h4 fw-bold mb-0 d-flex align-items-center">
        <i class="ki-duotone ki-passport fs-1 me-2 text-primary">
          <i class="path1"></i>
          <i class="path2"></i>
        </i>
        Maids Visit Visa
      </h2>

      <!-- Primary Action Button -->
      <button
        type="button"
        class="btn btn-primary d-flex align-items-center"
        :disabled="selectedRows.length === 0"
        @click="handleBulkAction"
      >
        <i class="ki-duotone ki-check-circle fs-2 me-2">
          <i class="path1"></i>
          <i class="path2"></i>
        </i>
        Make visa under customer
        <span v-if="selectedRows.length > 0" class="badge bg-white text-primary ms-2 shadow-sm">
          {{ selectedRows.length }}
        </span>
      </button>
    </div>

    <!-- Search and Filter Bar -->
    <div class="card visa-card mb-4 border-0 shadow-sm">
      <div class="card-body p-4">
        <div class="row g-4 align-items-end">
          <!-- Search by maid name -->
          <div class="col-md-4">
            <label class="form-label fw-semibold text-muted small text-uppercase mb-1">Maid Name</label>
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
                placeholder="Search by name..."
              />
            </div>
          </div>

          <!-- Search by nationality -->
          <div class="col-md-4">
            <label class="form-label fw-semibold text-muted small text-uppercase mb-1">Nationality</label>
            <div class="input-group">
              <span class="input-group-text border-end-0">
                <i class="ki-duotone ki-geolocation fs-1">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
              </span>
              <input
                v-model="searchNationality"
                type="text"
                class="form-control border-start-0 ps-0"
                placeholder="Search nationality..."
              />
            </div>
          </div>

          <!-- Checkbox filter -->
          <div class="col-md-4">
            <div class="form-check form-switch d-flex align-items-center mb-2">
              <input
                class="form-check-input fs-4 me-3"
                type="checkbox"
                role="switch"
                id="checkVisa"
                v-model="showWithExpiryOnly"
              />
              <label class="form-check-label fw-semibold" for="checkVisa">
                Only with expiry date
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Count & Export -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <p v-if="total || isLoading" class="text-muted mb-0">
        <i class="ki-duotone ki-element-11 me-1 fs-4">
          <i class="path1"></i>
          <i class="path2"></i>
          <i class="path3"></i>
          <i class="path4"></i>
        </i>
        Total Results: <strong>{{ total || 0 }}</strong>
      </p>

      <button
        v-if="isLoading || (data?.data && data.data.length > 0)"
        type="button"
        class="btn btn-success btn-sm"
        :disabled="isLoading"
        @click="exportToExcel"
      >
        <i class="ki-duotone ki-file-down me-1 fs-4">
          <i class="path1"></i>
          <i class="path2"></i>
        </i> 
        Export to Excel
      </button>
    </div>

    <!-- Table -->
    <div class="card visa-card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover visa-table mb-0">
            <thead>
              <tr>
                <th style="width: 40px">
                  <input
                    type="checkbox"
                    class="form-check-input"
                    :checked="allSelected"
                    @change="toggleSelectAll"
                  />
                </th>
                <th>
                  <i class="ki-duotone ki-user fs-5 me-1 text-primary">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Maid Name
                </th>
                <th>
                  <i class="ki-duotone ki-time fs-5 me-1 text-warning">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Days Left
                </th>
                <th>
                  <i class="ki-duotone ki-briefcase fs-5 me-1 text-info">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Passport #
                </th>
                <th>
                  <i class="ki-duotone ki-calendar fs-5 me-1 text-info">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Expiry Date
                </th>
                <th>
                  <i class="ki-duotone ki-shield-tick fs-5 me-1 text-success">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Status
                </th>
                <th>
                  <i class="ki-duotone ki-user-edit fs-5 me-1 text-muted">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Created By
                </th>
                <th>
                  <i class="ki-duotone ki-calendar-tick fs-5 me-1 text-muted">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Created At
                </th>
                <th>
                  <i class="ki-duotone ki-flag fs-5 me-1 text-warning">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Nationality
                </th>
              </tr>
            </thead>
            <tbody>
              <!-- Skeleton loader -->
              <template v-if="isLoading">
                <tr v-for="i in pageSize" :key="`skeleton-${i}`" class="skeleton-row">
                  <td>
                    <div class="skeleton skeleton-checkbox"></div>
                  </td>
                  <td>
                    <div class="skeleton skeleton-text" style="width: 70%"></div>
                  </td>
                  <td>
                    <div class="skeleton skeleton-badge" style="width: 60px"></div>
                  </td>
                  <td>
                    <div class="skeleton skeleton-text" style="width: 80%"></div>
                  </td>
                  <td>
                    <div class="skeleton skeleton-text" style="width: 75%"></div>
                  </td>
                  <td>
                    <div class="skeleton skeleton-badge" style="width: 70px"></div>
                  </td>
                  <td>
                    <div class="skeleton skeleton-text" style="width: 60%"></div>
                  </td>
                  <td>
                    <div class="skeleton skeleton-text" style="width: 65%"></div>
                  </td>
                  <td>
                    <div class="skeleton skeleton-text" style="width: 55%"></div>
                  </td>
                </tr>
              </template>

              <!-- Empty state -->
              <tr v-else-if="!isLoading && data && data.data && !data.data.length">
                <td colspan="9" class="text-center text-muted py-4">
                  <i class="ki-duotone ki-inbox fs-1 d-block mb-2">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  No data available
                </td>
              </tr>

              <!-- Actual data -->
              <tr v-else-if="data && data.data" v-for="row in data.data" :key="row.id">
                <td>
                  <input
                    type="checkbox"
                    class="form-check-input"
                    :checked="isSelected(row.id)"
                    @change="handleSelectionChange(row.id)"
                  />
                </td>
                <td>
                  <a
                    :href="`/page/maid-finance/${encodeURIComponent(row.name)}`"
                    class="text-primary text-decoration-none fw-medium"
                  >
                    <i class="ri-user-3-fill me-1"></i>
                    {{ row.name }}
                  </a>
                </td>
                <td>
                  <span
                    class="badge"
                    :class="{
                      'bg-danger': row.days_remaining && parseInt(row.days_remaining) <= 7,
                      'bg-warning text-dark': row.days_remaining && parseInt(row.days_remaining) > 7 && parseInt(row.days_remaining) <= 30,
                      'bg-success': row.days_remaining && parseInt(row.days_remaining) > 30,
                      'bg-secondary': !row.days_remaining
                    }"
                  >
                    {{ row.days_remaining || 'N/A' }}
                    <span v-if="row.days_remaining"> days</span>
                  </span>
                </td>
                <td>
                  <code class="passport-code">
                    {{ row.passport_number || '-' }}
                  </code>
                </td>
                <td>
                  <i class="ri-calendar-2-line me-1 text-info"></i>
                  {{ row.visit_visa_expired || '-' }}
                </td>
                <td>
                  <span
                    class="badge"
                    :class="{
                      'bg-success': row.maid_status?.toLowerCase() === 'approved' || row.maid_status?.toLowerCase() === 'active',
                      'bg-primary': row.maid_status?.toLowerCase() === 'hired',
                      'bg-warning text-dark': row.maid_status?.toLowerCase() === 'pending',
                      'bg-danger': row.maid_status?.toLowerCase() === 'rejected',
                      'bg-secondary': !row.maid_status
                    }"
                  >
                    {{ row.maid_status || 'Unknown' }}
                  </span>
                </td>
                <td class="text-muted small">{{ row.co_created_by || '-' }}</td>
                <td class="text-muted small">{{ row.co_created_at || '-' }}</td>
                <td>
                  <i class="ri-global-line me-1"></i>
                  {{ row.nationality || '-' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="isLoading || (data?.data && data.data.length > 0)" class="card-footer visa-footer">
        <nav class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <!-- Page size selector -->
          <div class="d-flex align-items-center">
            <label class="me-2 text-muted small">Rows per page:</label>
            <select
              class="form-select form-select-sm"
              style="width: auto"
              :value="pageSize"
              @change="handleSizeChange(Number($event.target.value))"
            >
              <option :value="10">10</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>

          <!-- Pagination controls -->
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <a
                class="page-link"
                href="#"
                @click.prevent="currentPage > 1 && handlePageChange(currentPage - 1)"
              >
                <i class="ki-duotone ki-left fs-5">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
                Previous
              </a>
            </li>

            <li v-if="visiblePages[0] > 1" class="page-item">
              <a 
                class="page-link" 
                href="#" 
                @click.prevent="handlePageChange(1)"
              >
                1
              </a>
            </li>

            <li v-if="visiblePages[0] > 2" class="page-item disabled">
              <span class="page-link">...</span>
            </li>

            <li
              v-for="pageNum in visiblePages"
              :key="pageNum"
              class="page-item"
              :class="{ active: pageNum === currentPage }"
            >
              <a 
                class="page-link"
                href="#" 
                @click.prevent="handlePageChange(pageNum)"
              >
                {{ pageNum }}
              </a>
            </li>

            <li v-if="visiblePages[visiblePages.length - 1] < totalPages - 1" class="page-item disabled">
              <span class="page-link">...</span>
            </li>

            <li v-if="visiblePages[visiblePages.length - 1] < totalPages" class="page-item">
              <a 
                class="page-link" 
                href="#" 
                @click.prevent="handlePageChange(totalPages)"
              >
                {{ totalPages }}
              </a>
            </li>

            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <a
                class="page-link"
                href="#"
                @click.prevent="currentPage < totalPages && handlePageChange(currentPage + 1)"
              >
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
            Page <strong>{{ currentPage }}</strong> of 
            <strong>{{ totalPages }}</strong>
            <span class="mx-2">•</span>
            <strong class="text-primary">{{ total }}</strong> total
          </div>
        </nav>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Theme-adaptive styling using Bootstrap CSS variables */
.visa-container {
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  margin: 1.5rem 3rem;
}

.visa-card {
  background-color: var(--bs-card-bg);
  border-color: var(--bs-border-color);
}

.visa-table {
  color: var(--bs-body-color);
}

.visa-table thead {
  background-color: var(--bs-body-bg);
  border-bottom: 2px solid var(--bs-border-color);
}

.visa-table thead th {
  padding: 1rem;
  font-weight: 600;
  color: var(--bs-emphasis-color);
  white-space: nowrap;
  font-size: 0.85rem;
  border-bottom: 2px solid var(--bs-border-color);
}

/* Add extra padding to checkbox column */
.visa-table th:first-child,
.visa-table td:first-child {
  padding-left: 1.25rem;
}

.visa-table tbody td {
  padding: 0.75rem 1rem;
  vertical-align: middle;
  border-bottom: 1px solid var(--bs-border-color);
}

.visa-table tbody tr:hover {
  background-color: var(--bs-tertiary-bg);
}

.visa-footer {
  background-color: var(--bs-body-bg);
  border-top: 1px solid var(--bs-border-color);
}

/* Form controls inherit theme colors */
.form-control,
.form-select {
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  border-color: var(--bs-border-color);
}

.form-control:focus,
.form-select:focus {
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  border-color: var(--bs-primary);
}

.form-control::placeholder {
  color: var(--bs-secondary-color);
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

/* Passport code styling */
.passport-code {
  background-color: var(--bs-tertiary-bg);
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.85rem;
  color: var(--bs-info);
  border: 1px solid var(--bs-border-color);
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
  .visa-table thead th,
  .visa-table tbody td {
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

.skeleton-checkbox {
  width: 20px;
  height: 20px;
  border-radius: 0.25rem;
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

