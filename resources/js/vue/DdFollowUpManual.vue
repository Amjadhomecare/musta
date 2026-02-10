<script setup>
import { ref, computed } from 'vue'
import { usePaginationQuery } from '@/composables/usePagination'

const formatDate = d => {
  if (!d) return '—'
  try {
    const date = new Date(d)
    if (isNaN(date)) return d
    return date.toLocaleDateString('en-GB', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    })
  } catch {
    return d
  }
}

const formatDateTime = d => (d ? new Date(d).toLocaleString() : '—')

/* ── Copy link to clipboard ── */
const copyLink = (ref) => {
  if (!ref) return
  const link = `https://sign.homecaremaids.ae/external/resign-rejection/${ref}`
  
  // Create a temporary textarea element
  const textarea = document.createElement('textarea')
  textarea.value = link
  textarea.style.position = 'fixed'
  textarea.style.opacity = '0'
  document.body.appendChild(textarea)
  
  try {
    textarea.select()
    textarea.setSelectionRange(0, 99999) // For mobile devices
    const successful = document.execCommand('copy')
    
    if (successful) {
      // Show success feedback (using Toastr if available, or fallback to alert)
      if (window.toastr) {
        toastr.success('Link copied to clipboard!')
      } else {
        alert('Link copied to clipboard!')
      }
    } else {
      throw new Error('Copy command failed')
    }
  } catch (err) {
    console.error('Failed to copy:', err)
    if (window.toastr) {
      toastr.error('Failed to copy link')
    } else {
      alert('Failed to copy link. Please copy manually: ' + link)
    }
  } finally {
    document.body.removeChild(textarea)
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
const filters = { 
  start_date: ref(''), 
  end_date: ref('')
}
const startDateFilter = computed({
  get: () => filters.start_date.value, set: v => (filters.start_date.value = v)
})
const endDateFilter = computed({
  get: () => filters.end_date.value, set: v => (filters.end_date.value = v)
})

/* ── Query ── */
const {
  data, isLoading, refetch, currentPage, pageSize, searchQuery, total,
  handlePageChange, handleSizeChange, exportToExcel
} = usePaginationQuery({
  apiUrl: '/dd-followup/manual',
  queryKeyPrefix: 'dd-follow-manual',
  filters
})

const totalPages = computed(() => Math.ceil((total.value || 0) / (pageSize.value || 10)))
const pageItems = computed(() => {
  const tp = totalPages.value
  const cp = currentPage.value
  if (tp <= 6) return Array.from({ length: tp }, (_, i) => i + 1)
  return buildPageItems(tp, cp, 1)
})

const rows = computed(() => data.value?.data ?? [])

/* ── Status badge ── */
const statusBadge = s => ({
  1: 'badge badge-light-info',
  2: 'badge badge-light-warning',
  3: 'badge badge-light-success',
  4: 'badge badge-light-danger',
}[s] || 'badge badge-light')

const statusText = s => ({
  1: 'Sent',
  2: 'No Reply',
  3: 'Replied',
  4: 'Manual Follow-Up',
}[s] || s)

/* ── DD Status badge ── */
const ddStatusBadge = s => ({
  0: 'badge badge-light-secondary',
  1: 'badge badge-light-success',
  2: 'badge badge-light-warning',
  3: 'badge badge-light-danger',
  4: 'badge badge-light-info',
}[s] || 'badge badge-light')

const ddStatusText = s => ({
  0: 'Created',
  1: 'Accepted',
  2: 'Pending',
  3: 'Rejected',
  4: 'Resign Requested',
}[s] || 'Unknown')
</script>

<template>
  <div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-xxl">

      <h1 class="d-flex align-items-center text-grey-900 fw-bolder fs-3 my-1 pb-4">
        DD Follow-Ups - Manual Follow-Up Required
      </h1>

      <div class="card">
        <!-- Card header -->
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
                placeholder="Search by ref, customer, notes..."
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

        <!-- Card body / table -->
        <div class="card-body pt-0">
          <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
              <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                  <th>DD Ref</th>
                  <th>Customer</th>
                  <th>Phone</th>
                  <th>Amount</th>
                  <th>DD Status</th>
                  <th>Attempts</th>
                  <th>Follow-Up Status</th>
                  <th>Rejection Reason</th>
                  <th>Notes</th>
                  <th>Created By</th>
                  <th>Updated By</th>
                  <th>Created At</th>
                  <th>Last Updated</th>
                </tr>
              </thead>

              <tbody class="fw-semibold text-gray-600">
                <tr v-if="isLoading">
                  <td :colspan="13" class="text-center py-10">
                    <div class="spinner-border text-primary me-2"></div>
                    Loading data, please wait...
                  </td>
                </tr>

                <tr v-for="row in rows" :key="row.id">
                  <td>
                    <a 
                      v-if="row.direct_debit?.ref" 
                      :href="`/vue/direct-debit?search=${row.direct_debit.ref}&page=1&perPage=10`"
                      class="text-primary fw-bold"
                    >{{ row.direct_debit.ref }}</a>
                    <span v-else>—</span>
                  </td>
                  <td>
                    <a 
                      v-if="row.direct_debit?.customer?.name" 
                      :href="`/customer/report/p4/${encodeURIComponent(row.direct_debit.customer.name)}`"
                      class="text-primary fw-bold"
                    >{{ row.direct_debit.customer.name }}</a>
                    <span v-else>—</span>
                  </td>
                  <td>{{ row.direct_debit?.phone ?? '—' }}</td>
                  <td>
                    <div class="d-flex flex-column gap-1">
                      <span class="fw-semibold">AED {{ row.direct_debit?.fixed_amount ?? '—' }}</span>
                      <button
                        v-if="row.direct_debit?.ref"
                        @click="copyLink(row.direct_debit.ref)"
                        class="btn btn-sm btn-light-primary"
                        :title="`Copy link: https://sign.homecaremaids.ae/external/resign-rejection/${row.direct_debit.ref}`"
                      >
                        <i class="ki-duotone ki-copy fs-5">
                          <span class="path1"></span>
                          <span class="path2"></span>
                        </i>
                        Copy Link
                      </button>
                    </div>
                  </td>
                  <td>
                    <span :class="ddStatusBadge(row.direct_debit?.status)">{{ ddStatusText(row.direct_debit?.status) }}</span>
                  </td>
                  <td>
                    <span class="badge badge-light-warning">{{ row.attempt_number }} attempts</span>
                  </td>
                  <td>
                    <span :class="statusBadge(row.follow_up_status)">{{ statusText(row.follow_up_status) }}</span>
                  </td>
                  <td class="text-danger">{{ row.direct_debit?.rejected_reason ?? '—' }}</td>
                  <td>{{ row.follow_up_notes ?? '—' }}</td>
                  <td>{{ row.direct_debit?.created_by ?? '—' }}</td>
                  <td>{{ row.direct_debit?.updated_by ?? '—' }}</td>
                  <td>{{ formatDateTime(row.direct_debit?.created_at) }}</td>
                  <td>{{ formatDateTime(row.updated_at) }}</td>
                </tr>

                <tr v-if="!isLoading && rows.length === 0">
                  <td :colspan="13" class="text-center py-10 text-gray-500">No records found.</td>
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
