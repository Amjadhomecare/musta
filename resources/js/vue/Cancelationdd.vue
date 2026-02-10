<script setup>
import { ref, computed, watch } from 'vue'
import { usePaginationQuery } from '@/composables/usePagination'


const formatDate = d => {
  if (!d) return '—'
  try {
    const date = new Date(d)
    if (isNaN(date)) return d // fallback if parsing fails
    // Example: "06-Jul-2025"
    return date.toLocaleDateString('en-GB', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    })
  } catch {
    return d
  }
}

/* ── pagination dots helper ── */
function buildPageItems(totalPages, current, delta = 1) {
  const range = []
  const left  = Math.max(2, current - delta)
  const right = Math.min(totalPages - 1, current + delta)
  range.push(1)
  if (left > 2) range.push('...')
  for (let i = left; i <= right; i++) range.push(i)
  if (right < totalPages - 1) range.push('...')
  if (totalPages > 1) range.push(totalPages)
  return range
}

/* ── Filters ── */
const filters = { start_date: ref(''), end_date: ref('') }
const startDateFilter = computed({
  get: () => filters.start_date.value, set: v => (filters.start_date.value = v)
})
const endDateFilter = computed({
  get: () => filters.end_date.value,   set: v => (filters.end_date.value = v)
})

/* ── Query (list-only) ── */
const {
  data, isLoading, refetch, currentPage, pageSize, searchQuery, total,
  handlePageChange, handleSizeChange, exportToExcel
} = usePaginationQuery({
  apiUrl        : '/cancelation-requests',
  queryKeyPrefix: 'cancelationdd',
  filters
})
const totalPages = computed(() => Math.ceil((total.value || 0) / (pageSize.value || 10)))
const pageItems  = computed(() => {
  const tp = totalPages.value
  const cp = currentPage.value
  if (tp <= 6) return Array.from({ length: tp }, (_, i) => i + 1)
  return buildPageItems(tp, cp, 1)
})

/* ── rows & selection (kept lightweight; remove if not needed) ── */
const rows        = computed(() => data.value?.data ?? [])
const selectedIds = ref([])
const allSelected = computed(() => rows.value.length && rows.value.every(r => selectedIds.value.includes(String(r.id))))
function toggleSelectAll () {
  selectedIds.value = allSelected.value ? [] : rows.value.map(r => String(r.id))
}
watch(rows, () => (selectedIds.value = []))

/* ── formatters ── */
const formatDateTime = d => (d ? new Date(d).toLocaleString() : '—')
const formatTask = t => ({ 1: 'Refund + Cancellation', 2: 'Cancellation only', 3: 'Refund only' }[t] || t)
const formatStatus = s => ({ 0: 'Created', 1: 'In Review', 2: 'Processing', 3: 'Rejected', 4: 'Closed' }[s] || s)
const statusBadge  = s => ({
  0: 'badge badge-light-warning',
  1: 'badge badge-light-info',
  2: 'badge badge-light-primary',
  3: 'badge badge-light-danger',
  4: 'badge badge-light-success',
}[s] || 'badge badge-light')
</script>

<template>
  <div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-xxl">

      <h1 class="d-flex align-items-center text-grey-900 fw-bolder fs-3 my-1 pb-4">
        CancelationDD
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
                placeholder="Type to Search ..."
              />
            </div>
          </div>

          <!-- actions (no Add button) -->
          <div class="card-toolbar">
            <div class="d-flex justify-content-end">
              <!-- filter btn -->
              <button
                class="btn btn-light-primary me-3"
                data-kt-menu-trigger="click"
                data-kt-menu-placement="bottom-end"
              >
                <i class="ki-duotone ki-filter fs-2">
                  <span class="path1"></span><span class="path2"></span>
                </i>Filter
              </button>

              <!-- filter dropdown -->
              <div
                id="kt-toolbar-filter"
                class="menu menu-sub menu-sub-dropdown w-300px w-md-325px"
                data-kt-menu="true"
              >
                <div class="px-7 py-5">
                  <div class="fs-4 text-gray-900 fw-bold">Filter Options</div>
                </div>
                <div class="separator border-gray-200"></div>
                <div class="px-7 py-5">
                  <div class="mb-5">
                    <label class="form-label fw-semibold">Start Date</label>
                    <input v-model="startDateFilter" type="date" class="form-control" />
                  </div>
                  <div class="mb-10">
                    <label class="form-label fw-semibold">End Date</label>
                    <input v-model="endDateFilter" type="date" class="form-control" />
                  </div>
                  <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" data-kt-menu-dismiss="true" @click="refetch">Apply</button>
                  </div>
                </div>
              </div>

              <!-- export only -->
              <button class="btn btn-light-primary" @click="exportToExcel">
                <i class="ki-duotone ki-exit-up fs-2"><span class="path1"></span><span class="path2"></span></i>
                Export
              </button>
            </div>
          </div>
        </div>

        <!-- total badge -->
        <div class="ps-8 pt-4">
          <span class="badge badge-light-secondary">Total: {{ total }}</span>
        </div>

        <!-- ========== Card body / table ========== -->
        <div class="card-body pt-0">
          <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
      <thead>
  <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
    <th class="w-10px pe-2">
      <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
        <input type="checkbox" class="form-check-input" :checked="allSelected" @change="toggleSelectAll" />
      </div>
    </th>

    <th>DD Ref</th>
    <th>Commences</th>
    <th>IBAN</th>
    <th>Account Title</th>
    <th>Center Ref</th>
    <th>Paying Bank</th>
    <th>Bank ID</th>
    <th>Amount</th>
    <th>Customer</th>
    <th>Task</th>
    <th>Status</th>
    <th>Note</th>
    <th>Created By</th>
    <th>Updated By</th>
    <th>Created</th>
  </tr>
</thead>

<tbody class="fw-semibold text-gray-600">
  <tr v-if="isLoading">
    <td :colspan="17" class="text-center py-10">
      <div class="spinner-border text-primary me-2"></div>
      Loading data, please wait...
    </td>
  </tr>

  <tr v-for="row in rows" :key="row.id">
    <td>
      <div class="form-check form-check-sm form-check-custom form-check-solid">
        <input type="checkbox" class="form-check-input" :value="String(row.id)" v-model="selectedIds" />
      </div>
    </td>


          <!-- From related directDebit -->
          <td>{{ row.direct_debit?.ref ?? '—' }}</td>
          <td>{{ formatDate(row.direct_debit?.commences_on) }}</td>
          <td>{{ row.direct_debit?.iban ?? '—' }}</td>
          <td>{{ row.direct_debit?.account_title ?? '—' }}</td>
          <td>{{ row.direct_debit?.center_bank_ref ?? '—' }}</td>
          <td>{{ row.direct_debit?.paying_bank_name ?? '—' }}</td>
          <td>{{ row.direct_debit?.paying_bank_id ?? '—' }}</td>
          <td>{{ row.direct_debit?.fixed_amount ?? '—' }}</td>

          <!-- Customer name from relation -->
          <td>{{ row.direct_debit?.customer?.name ?? '—' }}</td>

          <!-- Task / Status from cancelation row -->
          <td>{{ formatTask(row.task) }}</td>
          <td><span :class="statusBadge(row.status)">{{ formatStatus(row.status) }}</span></td>

          <td>{{ row.note ?? '—' }}</td>

          <!-- user names directly from CancelationDd -->
      <td>{{ row.created_by_user?.name ?? '—' }}</td>
      <td>{{ row.updated_by_user?.name ?? '—' }}</td>


          <td>{{ formatDateTime(row.created_at) }}</td>
        </tr>

        <tr v-if="!isLoading && rows.length === 0">
          <td :colspan="17" class="text-center py-10 text-gray-500">No records found.</td>
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
                  <option value="10">10</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
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
/* Page-specific tweaks (optional) */
</style>
