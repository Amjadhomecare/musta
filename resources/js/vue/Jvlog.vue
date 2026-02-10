<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { usePaginationQuery } from '@/composables/usePagination'

/* ---------- Helpers ---------- */
function buildPageItems(totalPages, current, delta = 1) {
  const range = []
  const left = Math.max(2, current - delta)
  const right = Math.min(totalPages - 1, current + delta)

  range.push(1)
  if (left > 2) range.push('...')
  for (let i = left; i <= right; i++) range.push(i)
  if (right < totalPages - 1) range.push('...')
  if (totalPages > 1) range.push(totalPages)

  return range
}
const fmtAmount = v => typeof v === 'number' || typeof v === 'string'
  ? Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  : '0.00'
const fmtDate = v => (v ? new Date(v).toLocaleString() : '—')

/* ---------- Filters ---------- */
const filters = { start_date: ref(''), end_date: ref('') }
const startDateFilter = computed({
  get: () => filters.start_date.value, set: v => (filters.start_date.value = v)
})
const endDateFilter = computed({
  get: () => filters.end_date.value,   set: v => (filters.end_date.value = v)
})

/* ---------- Pagination query ---------- */
const {
  data, isLoading, refetch, currentPage, pageSize, searchQuery, total,
  handlePageChange, handleSizeChange, exportToExcel
} = usePaginationQuery({
  apiUrl        : '/jvlog',
  queryKeyPrefix: 'jvlog',
  filters
})
const totalPages = computed(() => Math.ceil((total.value || 0) / (pageSize.value || 10)))
const pageItems = computed(() => {
  const tp = totalPages.value
  const cp = currentPage.value
  if (tp <= 6) return Array.from({ length: tp }, (_, i) => i + 1)
  return buildPageItems(tp, cp, 1)
})

/* ---------- Selection ---------- */
const selectedIds = ref([])
const rows = computed(() => data.value?.data ?? [])
const allSelected = computed(() =>
  rows.value.length && rows.value.every(r => selectedIds.value.includes(String(r.id)))
)
function toggleSelectAll () {
  selectedIds.value = allSelected.value ? [] : rows.value.map(r => String(r.id))
}
watch(rows, () => (selectedIds.value = []))
</script>

<template>
  <div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-xxl">

      <h1 class="d-flex align-items-center text-grey-900 fw-bolder fs-3 my-1 pb-4">
        JV Logs
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

          <!-- actions -->
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
                  <div class="mb-10">
                    <label class="form-label fs-5 fw-semibold mb-3">Date range:</label>
                    <div class="d-flex gap-2">
                      <input type="date" class="form-control" v-model="startDateFilter" />
                      <input type="date" class="form-control" v-model="endDateFilter" />
                    </div>
                  </div>
                  <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" data-kt-menu-dismiss="true" @click="refetch">Apply</button>
                  </div>
                </div>
              </div>

              <!-- export -->
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
                      <input
                        type="checkbox"
                        class="form-check-input"
                        :checked="allSelected"
                        @change="toggleSelectAll"
                      />
                    </div>
                  </th>
                  <th>Ref Code</th>
                  <th>Voucher Type</th>
                  <th>Line Type</th>
                  <th>Account</th>
                  <th>Maid</th>
                  <th>Amount (Before)</th>
                  <th>Amount (After)</th>
                  <th>Changed By</th>
                  <th>Changed At</th>
                  <th>Notes</th>
                </tr>
              </thead>

              <tbody class="fw-semibold text-gray-700">
                <tr v-if="isLoading">
                  <td :colspan="11" class="text-center py-10">
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
                        :value="String(row.id)"
                        v-model="selectedIds"
                      />
                    </div>
                  </td>

                  <!-- Ref code as link -->
                  <td>
                    <a :href="`/view/jv/selected/${row.ref_code ?? ''}`" class="text-primary text-hover-dark fw-bold">
                      {{ row.ref_code ?? '—' }}
                    </a>
                  </td>

                  <td>{{ row.voucher_type ?? '—' }}</td>
                  <td class="text-capitalize">{{ row.line_type ?? '—' }}</td>
                  <td>{{ row.account_name ?? (`#${row.ledger_id ?? '—'}`) }}</td>
                  <td>{{ row.maid_name ?? (`#${row.maid_id ?? '—'}`) }}</td>
                  <td>{{ fmtAmount(row.amount_before) }}</td>
                  <td class="fw-bold">{{ fmtAmount(row.amount_after) }}</td>
                  <td>{{ row.changed_by ?? '—' }}</td>
                  <td>{{ fmtDate(row.changed_at) }}</td>
                  <td class="text-muted">{{ row.notes ?? '—' }}</td>
                </tr>

                <tr v-if="!isLoading && rows.length === 0">
                  <td :colspan="11" class="text-center py-10 text-muted">
                    No data found.
                  </td>
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
                <li
                  v-for="it in pageItems"
                  :key="`p-${it}`"
                  :class="['page-item', { active: it === currentPage, disabled: it === '...' }]"
                >
                  <a v-if="it !== '...'" href="javascript:;" class="page-link" @click="handlePageChange(it)">
                    {{ it }}
                  </a>
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
/* Optional tweaks */
.table td,
.table th { white-space: nowrap; }
</style>
