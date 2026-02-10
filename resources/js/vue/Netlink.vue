<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { useMutation }        from '@tanstack/vue-query'
import { usePaginationQuery } from '@/composables/usePagination'
import AjaxSelect2 from '@/components/AjaxSelect2.vue'
import dayjs from 'dayjs'


/* ────────────────────────────────────────────────────────────────
  1. Pagination helpers
──────────────────────────────────────────────────────────────── */
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

/* ––– Filters ––– */
const filters = { start_date: ref(''), end_date: ref('') }
const startDateFilter = computed({
  get: () => filters.start_date.value,
  set: v => (filters.start_date.value = v)
})
const endDateFilter = computed({
  get: () => filters.end_date.value,
  set: v => (filters.end_date.value = v)
})

/* ––– Pagination query ––– */
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
  apiUrl        : '/netlink',
  queryKeyPrefix: 'netlink',
  filters
})

const totalPages = computed(() =>
  pageSize.value ? Math.ceil(total.value / pageSize.value) : 1
)

const pageItems = computed(() => {
  const tp = totalPages.value
  const cp = currentPage.value
  if (tp <= 6) return Array.from({ length: tp }, (_, i) => i + 1)
  return buildPageItems(tp, cp, 1)
})

/* ––– Rows + select-all ––– */
const selectedIds = ref([])
const rows        = computed(() => data.value?.data ?? [])

const allSelected = computed(() =>
  rows.value.length &&
  rows.value.every(r => selectedIds.value.includes(String(r.id)))
)

function toggleSelectAll () {
  selectedIds.value = allSelected.value ? [] : rows.value.map(r => String(r.id))
}

watch(rows, () => (selectedIds.value = []))

/* ────────────────────────────────────────────────────────────────
  2.  Modal + form (NetWorkLink request shape)
──────────────────────────────────────────────────────────────── */

const form = reactive({
  customer_id: null,
  maid_id: null,
  customer_email: '',      
  amount_value: null,
  expiry_date: dayjs().add(5, 'day').format('YYYY-MM-DD'),
  transaction_type: 'PURCHASE',
  skip_email_notification: false,
  note: ''
})



const isEditing    = ref(false)
const editId       = ref(null)
const modalLoading = ref(false)

/* ────────────────────────────────────────────────────────────────
  3.  Refund Modal + form
──────────────────────────────────────────────────────────────── */

const refundForm = reactive({
  amount_value: null,
  currency_code: 'AED'
})

const refundModalLoading = ref(false)
const refundOrderId = ref(null)
const refundOrderData = ref(null)

function resetForm () {
  Object.assign(form, {
    customer_id: null,
    maid_id: null,
    customer_email: '',   
    amount_value: null,
    expiry_date: dayjs().add(5, 'day').format('YYYY-MM-DD'),
    transaction_type: 'PURCHASE',
    skip_email_notification: false,
    note: ''
  })
}

function openModal () {
  isEditing.value = false
  editId.value    = null
  resetForm()
  bootstrap.Modal.getOrCreateInstance('#kt_modal_crud').show()
}

function showEditModal (item) {
  isEditing.value = true
  editId.value    = item.id

  Object.assign(form, {
    customer_id: item.customer_id ?? null,
    customer_email: item.customer_email ?? '',
    maid_id: item.maid_id ?? null,
    amount_value: item.amount_value ?? null,
    expiry_date: item.expiry_date ?? '',
    transaction_type: item.transaction_type ?? 'PURCHASE',
    skip_email_notification: !!item.skip_email_notification,
    note: item.note ?? ''
  })

  bootstrap.Modal.getOrCreateInstance('#kt_modal_crud').show()
}

/* ––– Save (create / update) ––– */
const { mutate: saveItem } = useMutation({
  mutationFn: payload =>
    editId.value
      ? axios.put(`/netlink/${editId.value}`, payload)
      : axios.post('/netlink', payload),
  onMutate  : () => (modalLoading.value = true),
  onSuccess : r => {
    Swal.fire('Success', r.data.message ?? 'Saved', 'success')
    refetch()
    bootstrap.Modal.getOrCreateInstance('#kt_modal_crud').hide()
  },
  onError   : e => {
    Swal.fire('Error', e.response?.data?.message ?? e.message, 'error')
  },
  onSettled : () => (modalLoading.value = false)
})

function handleSubmit () {

   const payload = { ...form }
  console.log('NetLink Payload:', payload)

  saveItem({ ...form })
}

/* ––– Refresh order status ––– */
const refreshingIds = ref([])

const { mutate: refreshStatus } = useMutation({
  mutationFn: (id) => {
    refreshingIds.value.push(id)
    return axios.post(`/netlink/${id}/refresh-status`)
  },
  onSuccess: (response, id) => {
    const data = response.data
    Swal.fire({
      title: 'Status Refreshed',
      html: `
        <div class="text-start">
          <p><strong>Old Status:</strong> ${data.old_status}</p>
          <p><strong>New Status:</strong> ${data.status_text}</p>
          <p><strong>State:</strong> ${data.state}</p>
        </div>
      `,
      icon: 'success'
    })
    refetch()
  },
  onError: (e, id) => {
    Swal.fire('Error', e.response?.data?.message ?? e.message, 'error')
  },
  onSettled: (_, __, id) => {
    refreshingIds.value = refreshingIds.value.filter(rid => rid !== id)
  }
})

function handleRefreshStatus(id) {
  Swal.fire({
    title: 'Refresh Order Status?',
    text: 'This will fetch the latest payment status from N-Genius',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, refresh it',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      refreshStatus(id)
    }
  })
}




function copyLink (url) {
  if (!url) return

  // Modern API available?
  if (navigator && navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(url)
      .then(() => {
        Swal.fire({
          icon: 'success',
          title: 'Copied!',
          text: 'Payment link copied to clipboard',
          timer: 1500,
          showConfirmButton: false
        })
      })
      .catch(() => {
        Swal.fire('Error', 'Could not copy the link', 'error')
      })

    return
  }

  // Fallback for older / insecure contexts (HTTP, some iframes, etc.)
  const textarea = document.createElement('textarea')
  textarea.value = url
  textarea.setAttribute('readonly', '')
  textarea.style.position = 'absolute'
  textarea.style.left = '-9999px'
  document.body.appendChild(textarea)

  textarea.select()
  try {
    const successful = document.execCommand('copy')
    if (successful) {
      Swal.fire({
        icon: 'success',
        title: 'Copied!',
        text: 'Payment link copied to clipboard',
        timer: 1500,
        showConfirmButton: false
      })
    } else {
      Swal.fire('Error', 'Could not copy the link', 'error')
    }
  } catch (e) {
    Swal.fire('Error', 'Could not copy the link', 'error')
  }

  document.body.removeChild(textarea)
}

/* ────────────────────────────────────────────────────────────────
  4.  Refund functionality
──────────────────────────────────────────────────────────────── */

function resetRefundForm() {
  Object.assign(refundForm, {
    amount_value: null,
    currency_code: 'AED'
  })
}

function openRefundModal(row) {
  refundOrderId.value = row.id
  refundOrderData.value = row
  
  // Pre-fill with the order amount
  refundForm.amount_value = row.amount_value
  refundForm.currency_code = 'AED'
  
  bootstrap.Modal.getOrCreateInstance('#kt_modal_refund').show()
}

const { mutate: processRefund } = useMutation({
  mutationFn: (payload) => axios.post(`/netlink/${refundOrderId.value}/refund`, payload),
  onMutate: () => (refundModalLoading.value = true),
  onSuccess: (response) => {
    const data = response.data
    Swal.fire({
      title: 'Refund Processed',
      html: `
        <div class="text-start">
          <p><strong>Status:</strong> ${data.state || 'SUCCESS'}</p>
          <p><strong>Amount:</strong> ${refundForm.amount_value} ${refundForm.currency_code}</p>
          <p><strong>Message:</strong> ${data.message || 'Refund processed successfully'}</p>
        </div>
      `,
      icon: 'success'
    })
    refetch()
    bootstrap.Modal.getOrCreateInstance('#kt_modal_refund').hide()
    resetRefundForm()
  },
  onError: (e) => {
    Swal.fire('Error', e.response?.data?.message ?? e.message, 'error')
  },
  onSettled: () => (refundModalLoading.value = false)
})

function handleRefundSubmit() {
  if (!refundForm.amount_value || refundForm.amount_value <= 0) {
    Swal.fire('Error', 'Please enter a valid refund amount', 'error')
    return
  }

  Swal.fire({
    title: 'Confirm Refund',
    html: `
      <div class="text-start">
        <p>Are you sure you want to process this refund?</p>
        <p><strong>Amount:</strong> ${refundForm.amount_value} ${refundForm.currency_code}</p>
        <p><strong>Order ID:</strong> ${refundOrderId.value}</p>
      </div>
    `,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, refund it',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      processRefund({
        amount_value: refundForm.amount_value,
        currency_code: refundForm.currency_code
      })
    }
  })
}

/* ────────────────────────────────────────────────────────────────
  5.  Status badge helper
──────────────────────────────────────────────────────────────── */

function getStatusBadgeClass(statusText) {
  const status = (statusText || '').toLowerCase()
  
  if (status.includes('paid') || status.includes('success')) {
    return 'badge-light-success'
  }
  if (status.includes('refund')) {
    return 'badge-light-warning'
  }
  if (status.includes('fail') || status.includes('decline')) {
    return 'badge-light-danger'
  }
  if (status.includes('cancel')) {
    return 'badge-light-dark'
  }
  if (status.includes('expire')) {
    return 'badge-light-secondary'
  }
  // Pending or unknown
  return 'badge-light-primary'
}

</script>

<template>
  <div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-xxl">
      <h1 class="d-flex align-items-center text-grey-900 fw-bolder fs-3 my-1 pb-4">
        NetLink
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
                </i>
                Filter
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
                    <label class="form-label fs-5 fw-semibold mb-3">Month:</label>
                    <select
                      class="form-select form-select-solid fw-bold"
                      data-placeholder="Select option"
                    >
                      <option></option>
                      <option value="01">January</option>
                      <option value="02">February</option>
                    </select>
                  </div>
                  <div class="d-flex justify-content-end">
                    <button
                      class="btn btn-primary"
                      data-kt-menu-dismiss="true"
                      @click="refetch"
                    >
                      Apply
                    </button>
                  </div>
                </div>
              </div>

              <!-- export + add -->
              <button class="btn btn-light-primary me-3" @click="exportToExcel">
                <i class="ki-duotone ki-exit-up fs-2">
                  <span class="path1"></span><span class="path2"></span>
                </i>
                Export
              </button>
              <button class="btn btn-primary" @click="openModal">
                + Add
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
                  <th>ID</th>
                  <th>Customer</th>
                  <th>Maid</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Note</th>
                  <th>Created By</th>
                  <th>Created At</th>
           

                  <th class="text-end">Actions</th>
                </tr>
              </thead>

              <tbody class="fw-semibold text-gray-600">
                <tr v-if="isLoading">
                  <td :colspan="7" class="text-center py-10">
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
                  <td>{{ row.id }}</td>
                      <td>
                      <a 
                        :href=" '/page/invoices/' + row.customer_name " 
                        target="_blank" 
                        class="text-primary text-decoration-underline"
                      >
                        {{ row.customer_name  }}
                      </a>
                    </td>

                  <td>{{ row.maid_name ?? row.maid_id ?? 'N/A' }}</td>

                  <td>
              <button 
                class="btn btn-sm btn-light-primary"
                @click="copyLink(row.payment_url)"
              >
                Copy Link ({{ row.amount_value ?? '0.00' }})
              </button>
            </td>

                             
                  <td>
                    <span class="badge" :class="getStatusBadgeClass(row.status_text)">
                      {{ row.status_text ?? row.status }}
                    </span>
                  </td>

                    <td>{{ row.note ?? 'N/A' }}</td>
                    <td>{{ row.created_by ?? 'N/A' }}</td>
                    <td>{{ row.created_at ?? 'N/A' }}</td>
        
                  <td class="text-end">
                    <button
                      class="btn btn-icon btn-bg-light btn-active-color-success btn-sm me-1"
                      @click="handleRefreshStatus(row.id)"
                      :disabled="refreshingIds.includes(row.id)"
                      title="Refresh order status"
                    >
                      <span v-if="refreshingIds.includes(row.id)" class="spinner-border spinner-border-sm"></span>
                      <i v-else class="ki-duotone ki-arrows-circle fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                      </i>
                    </button>
                    <button
                      class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm me-1"
                      @click="openRefundModal(row)"
                      :disabled="!row.amount_value || row.status_text === 'Refunded' || row.status_text === 'REFUNDED'"
                      title="Process refund"
                    >
                      <i class="ki-duotone ki-dollar fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                      </i>
                    </button>
                    <button
                      class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                      @click="showEditModal(row)"
                    >
                      <i class="ki-duotone ki-pencil fs-2">
                        <span class="path1"></span><span class="path2"></span>
                      </i>
                    </button>
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
                  <a
                    href="javascript:;"
                    class="page-link"
                    @click="handlePageChange(Math.max(1, currentPage - 1))"
                  >
                    <i class="previous"></i>
                  </a>
                </li>

                <!-- Dynamic Pages -->
                <li
                  v-for="it in pageItems"
                  :key="`p-${it}`"
                  :class="['page-item', { active: it === currentPage, disabled: it === '...' }]"
                >
                  <a
                    v-if="it !== '...'"
                    href="javascript:;"
                    class="page-link"
                    @click="handlePageChange(it)"
                  >
                    {{ it }}
                  </a>
                  <a v-else href="javascript:;" class="page-link">…</a>
                </li>

                <!-- Next -->
                <li :class="['page-item next', { disabled: currentPage === totalPages }]">
                  <a
                    href="javascript:;"
                    class="page-link"
                    @click="handlePageChange(Math.min(totalPages, currentPage + 1))"
                  >
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

  <!-- ========== Modal ========== -->
  <div class="modal fade" id="kt_modal_crud" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title">{{ isEditing ? 'Edit NetLink' : 'Add NetLink' }}</h3>
          <button class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal">
            <i class="ki-duotone ki-cross fs-1">
              <span class="path1"></span><span class="path2"></span>
            </i>
          </button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <!-- Customer -->
            <div class="col-md-6">
              <label class="form-label">Customer</label>
              <AjaxSelect2
                v-model="form.customer_id"
                class="form-control"
                url="/all-customers"
                placeholder="Select customer"
                dropdownParent="#kt_modal_crud"
                :mapResults="(data, params, perPage) => ({
                  results: (data?.items || []).map(i => ({
                    id: i.erp_id,
                    text: i.text
                  })),
                  pagination: {
                    more: (params.page || 1) * 30 < (data?.total_count || 0)
                  }
                })"
              />
              <small class="text-muted">Selected ID: {{ form.customer_id }}</small>
            </div>

            <!-- Maid -->
            <div class="col-md-6">
              <label class="form-label">Maid</label>
              <AjaxSelect2
                v-model="form.maid_id"
                class="form-control"
                url="/all/maids"
                placeholder="Select maid"
                dropdownParent="#kt_modal_crud"
                :mapResults="(data, params, perPage) => ({
                  results: (data?.items || []).map(i => ({
                    id: i.system_id,
                    text: i.text
                  })),
                  pagination: {
                    more: (params.page || 1) * 30 < (data?.total_count || 0)
                  }
                })"
              />
              <small class="text-muted">Selected ID: {{ form.maid_id }}</small>
            </div>

            <!-- Amount -->
            <div class="col-md-4">
              <label class="form-label">Amount (AED)</label>
              <input
                v-model.number="form.amount_value"
                type="number"
                min="0"
                step="0.01"
                class="form-control"
                placeholder="0.00"
         
              />
            </div>

            <!-- Expiry date -->
            <div class="col-md-4">
              <label class="form-label">Expiry Date</label>
              <input
                v-model="form.expiry_date"
                type="date"
                class="form-control"
              />
            </div>

            <!-- Transaction type -->
            <div class="col-md-4">
              <label class="form-label">Transaction Type</label>
              <select
                v-model="form.transaction_type"
                class="form-select"
              >
                <option value="PURCHASE">PURCHASE</option>
              </select>
            </div>

            <!-- Customer email -->
            <div class="col-12">
              <label class="form-label">Customer Email</label>
              <input
                v-model="form.customer_email"
                type="email"
                class="form-control"
                placeholder="Enter customer email"
              />
            </div>

            <!-- Skip email notification -->
            <div class="col-12">
              <div class="form-check form-switch">
                <input
                  id="skipEmail"
                  v-model="form.skip_email_notification"
                  class="form-check-input"
                  type="checkbox"
                />
                <label class="form-check-label" for="skipEmail">
                  Skip email notification (payment link only)
                </label>
              </div>
            </div>

            <!-- Note -->
            <div class="col-12">
              <label class="form-label">Note</label>
              <textarea
                v-model="form.note"
                rows="3"
                class="form-control"
                placeholder="Optional note about this payment link..."
              />
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
          <button
            class="btn btn-primary"
            :disabled="modalLoading"
            @click="handleSubmit"
          >
            <span v-if="modalLoading" class="spinner-border spinner-border-sm"></span>
            {{ isEditing ? 'Update' : 'Save' }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ========== Refund Modal ========== -->
  <div class="modal fade" id="kt_modal_refund" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title">Process Refund</h3>
          <button class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal">
            <i class="ki-duotone ki-cross fs-1">
              <span class="path1"></span><span class="path2"></span>
            </i>
          </button>
        </div>

        <div class="modal-body">
          <div v-if="refundOrderData" class="mb-6 p-4 bg-light rounded">
            <div class="d-flex justify-content-between mb-2">
              <span class="fw-bold">Order ID:</span>
              <span>{{ refundOrderData.id }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span class="fw-bold">Customer:</span>
              <span>{{ refundOrderData.customer_name }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span class="fw-bold">Original Amount:</span>
              <span class="text-primary fw-bold">{{ refundOrderData.amount_value }} AED</span>
            </div>
            <div class="d-flex justify-content-between">
              <span class="fw-bold">Status:</span>
              <span class="badge badge-light-info">{{ refundOrderData.status_text }}</span>
            </div>
          </div>

          <div class="row g-3">
            <!-- Refund Amount -->
            <div class="col-md-8">
              <label class="form-label required">Refund Amount</label>
              <input
                v-model.number="refundForm.amount_value"
                type="number"
                min="0"
                step="0.01"
                class="form-control"
                placeholder="0.00"
                readonly
              />
              <small class="text-muted">Enter the amount to refund in AED</small>
            </div>

            <!-- Currency (read-only) -->
            <div class="col-md-4">
              <label class="form-label">Currency</label>
              <input
                v-model="refundForm.currency_code"
                type="text"
                class="form-control"
                readonly
              />
            </div>
          </div>

          <!-- Warning alert -->
          <div class="alert alert-warning d-flex align-items-center mt-4" role="alert">
            <i class="ki-duotone ki-information-5 fs-2hx text-warning me-4">
              <span class="path1"></span>
              <span class="path2"></span>
              <span class="path3"></span>
            </i>
            <div class="d-flex flex-column">
              <h5 class="mb-1">Refund Warning</h5>
              <span>This action will process a refund through N-Genius payment gateway. Make sure the amount is correct before proceeding.</span>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button
            class="btn btn-warning"
            :disabled="refundModalLoading"
            @click="handleRefundSubmit"
          >
            <span v-if="refundModalLoading" class="spinner-border spinner-border-sm me-2"></span>
            <i v-else class="ki-duotone ki-dollar fs-2 me-1">
              <span class="path1"></span>
              <span class="path2"></span>
              <span class="path3"></span>
            </i>
            Process Refund
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Page-specific tweaks (optional) */
</style>
