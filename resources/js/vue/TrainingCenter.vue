<script setup>
import { ref, reactive, computed } from 'vue'
import dayjs from 'dayjs'
import axios from 'axios'
import { usePaginationQuery } from '@/composables/usePagination'
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || ''

const currentUser = window.Laravel?.user

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
  start_date: ref(''),
  end_date: ref('')
}

const startDateFilter = computed({
  get: () => filters.start_date.value,
  set: (val) => (filters.start_date.value = val)
})

const endDateFilter = computed({
  get: () => filters.end_date.value,
  set: (val) => (filters.end_date.value = val)
})


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
  apiUrl: `${apiBaseUrl}/TrainingInv/list/`,
  queryKeyPrefix: 'list-training-inv',
  filters
})

// Debug logging
import { watch } from 'vue'
watch(data, (newData) => {
  console.log('TrainingCenter API URL:', `${apiBaseUrl}/TrainingInv/list/`)
  console.log('TrainingCenter data loaded:', newData)
})
watch(isLoading, (newVal) => {
  console.log('TrainingCenter loading state:', newVal)
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

const modalVisible = ref(false)
const modalLoading = ref(false)
const isEditing = ref(false)
const editId = ref(null)
const form = reactive({
  date: '',
  maidName: '',
  customerName: '',
  amount: null,
  ref: '',
  branch: '',
  extra: null,
})

function getFormPayload({ withUser = false, isEdit = false } = {}) {
  const payload = {
    date: form.date || null,
    maidName: form.maidName || null,
    customerName: form.customerName || null,
    amount: form.amount || null,
    ref: form.ref || null,
    branch: form.branch || null,
    extra: form.extra,
    status: form.status || null,
    amountPaid: form.amountPaid || null
  }

  if (withUser) {
    if (isEdit) {
      payload.updatedBy = currentUser?.name || null
    } else {
      payload.createdBy = currentUser?.name || null
    }
  }
  return payload
}

function resetForm() {
  Object.assign(form, {
    date: dayjs().format('YYYY-MM-DD'),
    maidName: '',
    customerName: '',
    amount: null,
    ref: '',
    branch: '',
    extra: null,
    status: 'unpaid',
  })
  editId.value = null
}

function showAddModal() {
  resetForm()
  isEditing.value = false
  modalVisible.value = true
}

function showEditModal(row) {
  Object.assign(form, {
    ...row,
    date: row.date ? dayjs(row.date).format('YYYY-MM-DD') : dayjs().format('YYYY-MM-DD'),
  })
  editId.value = row.id
  isEditing.value = true
  modalVisible.value = true
}

async function handleSubmit() {
  modalLoading.value = true
  try {
    if (isEditing.value) {
      await axios.put(
        `${apiBaseUrl}/TrainingInv/${editId.value}`,
        {
          id: editId.value,
          ...getFormPayload({ withUser: true, isEdit: true })
        }
      )
      showToast('Record updated!', 'success')
    } else {
      await axios.post(
        `${apiBaseUrl}/TrainingInv`,
        getFormPayload({ withUser: true })
      )
      showToast('Record added!', 'success')
    }
    modalVisible.value = false
    await refetch()
  } catch (e) {
    showToast(e.response?.data?.title || e.message || 'Error', 'danger')
  } finally {
    modalLoading.value = false
  }
}

async function handleDelete(row) {
  if (!confirm('Are you sure you want to delete this record?')) return

  try {
    await axios.delete(`${apiBaseUrl}/TrainingInv/${row.id}`)
    showToast('Deleted!', 'success')
    await refetch()
  } catch (e) {
    showToast('Failed to delete record', 'danger')
  }
}


const selectedRows = ref([])

function toggleSelection(id) {
  const index = selectedRows.value.indexOf(id)
  if (index === -1) {
    selectedRows.value.push(id)
  } else {
    selectedRows.value.splice(index, 1)
  }
}

function toggleAllSelection() {
  if (!data.value?.data) return
  
  if (selectedRows.value.length === data.value.data.length) {
    selectedRows.value = []
  } else {
    selectedRows.value = data.value.data.map(row => row.id)
  }
}

async function handleBulkStatus(status) {
  if (!selectedRows.value.length) {
    showToast('Please select at least one record!', 'warning')
    return
  }
  try {
    await axios.put(
      `${apiBaseUrl}/TrainingInv/bulk-status`,
      {
        ids: selectedRows.value,
        status,
        updatedBy: currentUser?.name || null
      }
    )
    showToast(`Status updated to ${status} for selected records.`, 'success')
    selectedRows.value = []
    await refetch()
  } catch (e) {
    showToast(e.response?.data?.title || e.message || 'Error', 'danger')
  }
}

</script>

<template>
  <div class="training-container">
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
        <i class="ki-duotone ki-teacher fs-1 me-2 text-primary">
          <i class="path1"></i>
          <i class="path2"></i>
        </i>
        Training Center Invoice
      </h2>
    </div>

    <!-- Controls Toolbar -->
    <div class="card training-card mb-4 border-0 shadow-sm">
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

          <!-- Date Range -->
          <div>
            <label class="form-label fw-semibold text-muted small text-uppercase mb-1">Date Range</label>
            <div class="d-flex gap-2">
              <input
                v-model="startDateFilter"
                type="date"
                class="form-control"
                placeholder="Start date"
                @change="refetch"
              />
              <input
                v-model="endDateFilter"
                type="date"
                class="form-control"
                placeholder="End date"
                @change="refetch"
              />
            </div>
          </div>

          <!-- Actions -->
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary d-flex align-items-center" @click="showAddModal">
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

        <!-- Bulk Actions -->
        <div class="mt-3 pt-3 border-top d-flex gap-2" v-if="selectedRows.length > 0">
          <span class="align-self-center me-2 text-muted small">{{ selectedRows.length }} selected</span>
          <button
            type="button"
            class="btn btn-sm btn-success"
            @click="handleBulkStatus('paid')"
          >
            <i class="ki-duotone ki-check-circle fs-4 me-1">
              <i class="path1"></i>
              <i class="path2"></i>
            </i>
            Mark as Paid
          </button>
          <button
            type="button"
            class="btn btn-sm btn-warning text-dark"
            @click="handleBulkStatus('unpaid')"
          >
            <i class="ki-duotone ki-cross-circle fs-4 me-1">
              <i class="path1"></i>
              <i class="path2"></i>
            </i>
            Mark as Unpaid
          </button>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="card training-card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover training-table mb-0">
            <thead>
              <tr>
                <th style="width: 40px">
                  <div class="form-check">
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      :checked="data && data.data && selectedRows.length === data.data.length && data.data.length > 0"
                      @change="toggleAllSelection"
                    >
                  </div>
                </th>
                <th>
                  <i class="ki-duotone ki-calendar fs-5 me-1 text-primary">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Date
                </th>
                <th>
                  <i class="ki-duotone ki-user fs-5 me-1 text-info">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Maid
                </th>
                <th>
                  <i class="ki-duotone ki-profile-user fs-5 me-1 text-success">
                    <i class="path1"></i>
                    <i class="path2"></i>
                    <i class="path3"></i>
                    <i class="path4"></i>
                  </i>
                  Customer
                </th>
                <th>
                  <i class="ki-duotone ki-dollar fs-5 me-1 text-warning">
                    <i class="path1"></i>
                    <i class="path2"></i>
                    <i class="path3"></i>
                  </i>
                  Amount
                </th>
                <th>Status</th>
                <th style="width: 160px">Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Skeleton loader -->
              <template v-if="isLoading">
                <tr v-for="i in pageSize" :key="`skeleton-${i}`" class="skeleton-row">
                  <td><div class="skeleton skeleton-text" style="width: 20px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 80px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 70%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 70%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 60px"></div></td>
                  <td><div class="skeleton skeleton-badge" style="width: 60px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 100px"></div></td>
                </tr>
              </template>

              <!-- Empty state -->
              <tr v-else-if="!isLoading && data && data.data && !data.data.length">
                <td colspan="7" class="text-center text-muted py-4">
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
                  <div class="form-check">
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      :checked="selectedRows.includes(row.id)"
                      @change="toggleSelection(row.id)"
                    >
                  </div>
                </td>
                <td>{{ row.date }}</td>
                <td>
                  <a :href="`/erp/training/invoice/${row.id}`" target="_blank" class="text-primary text-decoration-none hover-underline">
                    {{ row.maidName }}
                  </a>
                </td>
                <td>{{ row.customerName }}</td>
                <td>{{ row.amount }}</td>
                <td>
                  <span class="badge" :class="{
                    'bg-success': row.status === 'paid',
                    'bg-warning text-dark': row.status === 'unpaid'
                  }">
                    {{ row.status }}
                  </span>
                </td>
                <td>
                  <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-primary" @click="showEditModal(row)">
                      Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" @click="handleDelete(row)">
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination Footer -->
      <div v-if="isLoading || (data && total > 0)" class="card-footer training-footer">
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
    <div v-if="modalVisible" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content training-modal">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">
              <i class="ki-duotone ki-teacher fs-1 me-2 text-primary">
                <i class="path1"></i>
                <i class="path2"></i>
              </i>
              {{ isEditing ? 'Edit Record' : 'Add Record' }}
            </h5>
            <button type="button" class="btn-close" @click="modalVisible = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="handleSubmit">
              <div class="mb-3">
                <label class="form-label fw-semibold">Date</label>
                <input v-model="form.date" type="date" class="form-control" />
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Maid Name</label>
                <input v-model="form.maidName" type="text" class="form-control" />
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Customer Name</label>
                <input v-model="form.customerName" type="text" class="form-control" />
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Amount</label>
                <input v-model="form.amount" type="number" class="form-control" />
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="modalVisible = false">Cancel</button>
            <button type="button" class="btn btn-primary" :disabled="modalLoading" @click="handleSubmit">
              <span v-if="modalLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              {{ isEditing ? 'Update' : 'Create' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Theme-adaptive styling using Bootstrap CSS variables */
.training-container {
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  margin: 1.5rem 3rem;
}

.training-card,
.training-modal {
  background-color: var(--bs-card-bg);
  border-color: var(--bs-border-color);
}

.training-table {
  color: var(--bs-body-color);
}

.training-table thead {
  background-color: var(--bs-body-bg);
}

.training-table thead th {
  padding: 1rem;
  font-weight: 600;
  color: var(--bs-emphasis-color);
  white-space: nowrap;
  font-size: 0.85rem;
  border-bottom: 2px solid var(--bs-border-color);
}

.training-table th:first-child,
.training-table td:first-child {
  padding-left: 1.25rem;
}

.training-table tbody td {
  padding: 0.75rem 1rem;
  vertical-align: middle;
  border-bottom: 1px solid var(--bs-border-color);
}

.training-table tbody tr:hover {
  background-color: var(--bs-tertiary-bg);
}

.training-footer {
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
  .training-container {
    margin: 1rem;
  }

  .training-table thead th,
  .training-table tbody td {
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

.skeleton-badge {
  height: 1.25rem;
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

.hover-underline:hover {
  text-decoration: underline !important;
}
</style>
