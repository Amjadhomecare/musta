<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { useMutation } from '@tanstack/vue-query'
import { usePaginationQuery } from '@/composables/usePagination'

function buildPageItems(totalPages, current, delta = 1) {
  const range = [];
  const left = Math.max(2, current - delta);
  const right = Math.min(totalPages - 1, current + delta);

  range.push(1);
  if (left > 2) range.push('...');
  for (let i = left; i <= right; i++) range.push(i);
  if (right < totalPages - 1) range.push('...');
  if (totalPages > 1) range.push(totalPages);

  return range;
}

const pageItems = computed(() => {
  const tp = totalPages.value;
  const cp = currentPage.value;
  if (tp <= 6) return Array.from({ length: tp }, (_, i) => i + 1);
  return buildPageItems(tp, cp, 1);
});

/* ––– Filters ––– */
const filters = { start_date: ref(''), end_date: ref('') }
const startDateFilter = computed({
  get: () => filters.start_date.value, set: v => (filters.start_date.value = v)
})
const endDateFilter = computed({
  get: () => filters.end_date.value,   set: v => (filters.end_date.value = v)
})

/* ––– Pagination query ––– */
const {
  data, isLoading, refetch, currentPage, pageSize, searchQuery, total,
  handlePageChange, handleSizeChange, exportToExcel
} = usePaginationQuery({
  apiUrl        : '/api/refundlist',
  queryKeyPrefix: 'refundlist',
  filters
})
const totalPages = computed(() => Math.ceil(total.value / pageSize.value))

const rows = computed(() => data.value?.data ?? [])

/* ––– Checkbox selection ––– */
const selectedIds = ref([])

const allSelected = computed(() =>
  rows.value.length > 0 && rows.value.every(r => selectedIds.value.includes(r.id))
)

function toggleSelectAll() {
  if (allSelected.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = rows.value.map(r => r.id)
  }
}

// Clear selection when page changes
watch(rows, () => (selectedIds.value = []))

/* ––– Bulk approve mutation ––– */
const { isPending: isApproving, mutate: bulkApprove } = useMutation({
  mutationFn: (ids) => axios.post('/api/refundlist/bulk-approve', { ids }),
  onSuccess: (response) => {
    Swal.fire('Success!', response.data.message, 'success')
    selectedIds.value = []
    refetch()
  },
  onError: (err) => {
    const msg = err?.response?.data?.message || err.message || 'Failed to approve refunds'
    Swal.fire('Error!', msg, 'error')
  }
})

function handleBulkApprove() {
  if (selectedIds.value.length === 0) {
    return Swal.fire('Warning', 'Please select at least one refund to approve', 'warning')
  }
  
  Swal.fire({
    title: 'Approve Refunds?',
    text: `Are you sure you want to approve ${selectedIds.value.length} refund(s)?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, Approve',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      bulkApprove(selectedIds.value)
    }
  })
}

/* ––– Helper functions ––– */
const formatMoney = v => v == null ? '—'
  : new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(v)

const formatDate = d => {
  if (!d) return '—'
  const date = new Date(d)
  return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

const getStatusBadge = (status) => {
  switch(status) {
    case 0: return 'badge badge-warning'
    case 1: return 'badge badge-success'
    default: return 'badge badge-secondary'
  }
}

const getStatusLabel = (status) => {
  switch(status) {
    case 0: return 'Requested'
    case 1: return 'Approved'
    default: return 'Unknown'
  }
}

</script>

<template>
  <div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-xxl">

      <h1 class="d-flex align-items-center text-grey-900 fw-bolder fs-3 my-1 pb-4">
        Refund List
      </h1>

      <div class="card">
        <!-- ========== Card header ========== -->
        <div class="card-header border-0 pt-6">
          <!-- search -->
          <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
              <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                <span class="path1"></span><span class="path2"></span>
              </i>
              <input
                v-model="searchQuery"
                type="text"
                class="form-control form-control-solid w-250px ps-12"
                placeholder="Search customer, amount..."
              />
            </div>
          </div>

          <!-- actions -->
          <div class="card-toolbar">
            <div class="d-flex justify-content-end gap-2">
              <!-- Bulk approve button -->
              <button 
                v-if="selectedIds.length > 0"
                class="btn btn-success me-3" 
                @click="handleBulkApprove"
                :disabled="isApproving"
              >
                <span v-if="isApproving" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="ki-duotone ki-check fs-2 me-1">
                  <span class="path1"></span><span class="path2"></span>
                </i>
                Approve ({{ selectedIds.length }})
              </button>
              
              <!-- export -->
              <button class="btn btn-light-primary me-3" @click="exportToExcel">
                <i class="ki-duotone ki-exit-up fs-2"><span class="path1"></span><span class="path2"></span></i>
                Export
              </button>
            </div>
          </div>
        </div>

        <!-- total badge -->
        <div class="ps-8 pt-4">
          <span class="badge badge-light-secondary">Total: {{ total }}</span>
          <span v-if="selectedIds.length > 0" class="badge badge-light-primary ms-2">
            Selected: {{ selectedIds.length }}
          </span>
        </div>

        <!-- ========== Card body / table ========== -->
        <div class="card-body pt-0">
          <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
              <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                  <th class="w-10px pe-2">
                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                      <input
                        type="checkbox"
                        class="form-check-input"
                        :checked="allSelected"
                        @change="toggleSelectAll"
                      />
                    </div>
                  </th>
                  <th>ID</th>
                  <th>Customer</th>
                  <th>DD Reference</th>
                  <th class="text-end">Amount</th>
                  <th>Note</th>
                  <th>Status</th>
                  <th>Created By</th>
                  <th>Created At</th>
                </tr>
              </thead>

              <tbody class="fw-semibold text-gray-600">
                <tr v-if="isLoading">
                  <td :colspan="9" class="text-center py-10">
                    <div class="spinner-border text-primary me-2"></div>
                    Loading data, please wait...
                  </td>
                </tr>

                <tr v-for="row in rows" :key="row.id">
                  <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                      <input
                        type="checkbox"
                        class="form-check-input"
                        :value="row.id"
                        v-model="selectedIds"
                      />
                    </div>
                  </td>
                  <td>{{ row.id }}</td>
                  <td>
                    <a v-if="row.direct_debit?.customer" 
                       :href="`/customer/report/p4/${row.direct_debit.customer.name}`" 
                       class="text-primary fw-bold">
                      {{ row.direct_debit.customer.name }}
                    </a>
                    <span v-else class="text-muted">—</span>
                  </td>
                  <td>
                    <a v-if="row.direct_debit?.ref" 
                       :href="`/vue/direct-debit?search=${row.direct_debit.ref}`" 
                       target="_blank"
                       class="text-gray-800">
                      {{ row.direct_debit.ref }}
                    </a>
                    <span v-else class="text-muted">—</span>
                  </td>
                  <td class="text-end">{{ formatMoney(row.amount) }}</td>
                  <td>
                    <span v-if="row.note" :title="row.note">
                      {{ row.note.length > 50 ? row.note.substring(0, 50) + '...' : row.note }}
                    </span>
                    <span v-else class="text-muted">—</span>
                  </td>
                  <td>
                    <span :class="getStatusBadge(row.status)">
                      {{ getStatusLabel(row.status) }}
                    </span>
                  </td>
                  <td>{{ row.created_by || '—' }}</td>
                  <td>{{ formatDate(row.created_at) }}</td>
                </tr>
              </tbody>
            </table>

            <!-- pagination -->
            <div class="d-flex justify-content-between align-items-center mt-5 flex-wrap gap-3">
              <div>
                <label class="me-2 fw-semibold">Page size:</label>
                <select
                  v-model="pageSize"
                  @change="handleSizeChange(parseInt($event.target.value))"
                  class="form-select form-select-sm w-auto d-inline-block"
                >
                  <option value="10">10</option><option value="25">25</option>
                  <option value="50">50</option><option value="100">100</option>
                </select>
              </div>

        <ul class="pagination mb-0">
              <!-- Previous -->
              <li :class="['page-item previous', { disabled: currentPage === 1 }]">
                <a href="javascript:;" class="page-link" @click="handlePageChange(Math.max(1, currentPage - 1))">
                  <i class="previous"></i>
                </a>
              </li>

              <!-- Dynamic Pages -->
              <li v-for="it in pageItems" :key="`p-${it}`"
                  :class="['page-item', { active: it === currentPage, disabled: it === '...' }]">
                <a v-if="it !== '...'" href="javascript:;" class="page-link" @click="handlePageChange(it)">{{ it }}</a>
                <a v-else href="javascript:;" class="page-link">…</a>
              </li>

              <!-- Next -->
              <li :class="['page-item next', { disabled: currentPage === totalPages }]">
                <a href="javascript:;" class="page-link" @click="handlePageChange(Math.min(totalPages, currentPage + 1))">
                  <i class="next"></i>
                </a>
              </li>
            </ul>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Page‑specific tweaks (optional) */
</style>