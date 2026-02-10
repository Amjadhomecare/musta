<script setup>
import { ref, reactive, computed, watch, onBeforeUnmount } from 'vue'
import { useMutation }        from '@tanstack/vue-query'
import { usePaginationQuery } from '@/composables/usePagination'
import * as XLSX from 'xlsx'
import dayjs  from 'dayjs'



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


const exportToExcel = () => {
  const rowsArr = data.value?.data ?? []
  if (!rowsArr.length) {
    console.warn('No data to export')
    return
  }

  // Map to human-readable columns (use maid_name; fallback to #maid_id)
  const mapped = rowsArr.map(r => ({
    Date          : r.date ?? '',
    Maid          : (r.maid_name && String(r.maid_name).trim().length)
                      ? r.maid_name
                      : (r.maid_id ? `#${r.maid_id}` : '—'),
    Note          : r.note ?? '',
    Amount        : Number(r.amount ?? 0),
    Transaction   : txLabel(r.transaction),
    PaymentMethod : methodLabel(r.payment_method),
    Status        : statusLabel(r.status),
    Attachment    : r.attachment ?? '',
    CreatedBy     : r.created_by ?? '',
    UpdatedBy     : r.updated_by ?? '',
    CreatedAt     : r.created_at ?? '',
    UpdatedAt     : r.updated_at ?? ''
  }))

  const ws = XLSX.utils.json_to_sheet(mapped)
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'Payment Orders')

  // Nice filename (e.g., payorder-2025-09-08.xlsx)
  const fname = `payorder-${new Date().toISOString().slice(0,10)}.xlsx`
  XLSX.writeFile(wb, fname)
}


/* Filters */
const filters = { 
  date_from: ref(''), 
  date_to: ref(''),
  status: ref('')     
}

const startDateFilter = computed({ get: () => filters.date_from.value, set: v => (filters.date_from.value = v) })
const endDateFilter   = computed({ get: () => filters.date_to.value,   set: v => (filters.date_to.value = v) })
const statusFilter    = computed({ get: () => filters.status.value,    set: v => (filters.status.value = v) })

/* Pagination */
const {
  data, isLoading, refetch, currentPage, pageSize, searchQuery, total,
  handlePageChange, handleSizeChange,
} = usePaginationQuery({
  apiUrl        : '/payment-orders',
  queryKeyPrefix: 'payorder',
  filters
})
const rows       = computed(() => data.value?.data ?? [])
const totalPages = computed(() => Math.max(1, Math.ceil((total.value || 0) / (pageSize.value || 10))))

/* Select-all */
const selectedIds = ref([])
const allSelected = computed(() => rows.value.length && rows.value.every(r => selectedIds.value.includes(String(r.id))))
function toggleSelectAll () { selectedIds.value = allSelected.value ? [] : rows.value.map(r => String(r.id)) }
watch(rows, () => (selectedIds.value = []))

/* Labels */
const txLabel = v => ({
  0: 'Visa with fine',
  1: 'Visa',
  2: 'Fine',
  3: 'Others'
}[v] ?? v)
const methodLabel = v => ({ 0: 'Noqodi', 1: 'Card' }[v] ?? v)
const statusLabel = v => ({ 0: 'Pending', 1: 'Approved', 2: 'Rejected' }[v] ?? v)

/* Modal + form */
const isEditing    = ref(false)
const editId       = ref(null)
const modalLoading = ref(false)
const fileObj      = ref(null)

const form = reactive({
  id            : null,
  date          :  dayjs().format('YYYY-MM-DD'),
  maid_id       : null,
  amount        : '',
  transaction   : 1,
  payment_method: 0,
  status        : 0,
  note          : '',
})

function resetForm () {
  Object.assign(form, {
    id: null, date: dayjs().format('YYYY-MM-DD'), maid_id: null, amount: '',
    transaction: 1, payment_method: 0, status: 0, note: ''
  })
  fileObj.value = null
}

/* Select2 (maids) — backend /all/maids stays EXACTLY as you wrote */
function initMaidSelect (preId = null, preText = '') {
  const $sel = $('#maid_id_select')
  if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy')

  $sel.select2({
    dropdownParent : $('#kt_modal_crud'),
    placeholder    : 'Select maid',
    allowClear     : true,
    width          : '100%',
    ajax: {
      url      : '/all/maids',
      delay    : 250,
      dataType : 'json',
      data: params => ({ search: params.term, page: params.page || 1 }),
      processResults: ({ items }) => ({
        results: items.map(i => ({ id: i.id, text: i.text, erp_id: i.system_id }))
      }),
      cache: true
    },
    templateResult    : r => r.loading ? r.text : r.text,
    templateSelection : r => r.text || r.id,
    escapeMarkup      : m => m
  })
  .on('select2:select', e => { form.maid_id = e.params.data.erp_id })
  .on('select2:clear',  () => { form.maid_id = null })

  if (preId && preText) {
    const opt = new Option(preText, preText, true, true)
    $(opt).data('data', { id: preText, text: preText, erp_id: preId })
    $sel.append(opt).trigger('change')
    form.maid_id = preId
  }
}
function destroyMaidSelect () {
  const $sel = $('#maid_id_select')
  if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy')
}

/* Modal open/edit */
function openModal () {
  isEditing.value = false; editId.value = null; resetForm()
  const modal = bootstrap.Modal.getOrCreateInstance('#kt_modal_crud'); modal.show()
  setTimeout(() => initMaidSelect(), 0)
}
function showEditModal (row) {
  isEditing.value = true; editId.value = row.id
  Object.assign(form, {
    id            : row.id,
    date          : row.date ?? '',
    maid_id       : row.maid_id ?? null,
    amount        : row.amount ?? '',
    transaction   : row.transaction ?? 1,
    payment_method: row.payment_method ?? 0,
    status        : row.status ?? 0,
    note          : row.note ?? ''
  })
  const modal = bootstrap.Modal.getOrCreateInstance('#kt_modal_crud'); modal.show()
  setTimeout(() => initMaidSelect(row.maid_id, row.maid_name || row.name || `#${row.maid_id}`), 0)
}
onBeforeUnmount(() => destroyMaidSelect())

/* Save (multipart ONLY, attachment_file required on create, optional on update) */
const { mutate: saveItem } = useMutation({
  mutationFn: () => {
    const fd = new FormData()
    if (form.id) fd.append('id', form.id)
    fd.append('date', form.date)
    if (form.maid_id) fd.append('maid_id', form.maid_id)
    fd.append('amount', String(Number(form.amount || 0)))
    fd.append('transaction', String(Number(form.transaction)))
    fd.append('payment_method', String(Number(form.payment_method)))
    fd.append('status', String(Number(form.status)))
    if (form.note) fd.append('note', form.note)

    // File REQUIRED on create; OPTIONAL on update
    if (!form.id && !fileObj.value) {
      return Promise.reject(new Error('Please choose an attachment file.'))
    }
    if (fileObj.value) {
      fd.append('attachment_file', fileObj.value)
    }

    return axios.post('/payment-orders/store-or-update', fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  },
  onMutate  : () => (modalLoading.value = true),
  onSuccess : r => {
    Swal.fire('Success', r.data.message ?? 'Saved', 'success')
    refetch()
    bootstrap.Modal.getOrCreateInstance('#kt_modal_crud').hide()
    destroyMaidSelect()
  },
  onError   : e => Swal.fire('Error', e.response?.data?.message ?? e.message, 'error'),
  onSettled : () => (modalLoading.value = false)
})
function handleSubmit () { saveItem() }

/* File change */
function onFileChange (e) {
  const f = e.target.files?.[0] ?? null
  fileObj.value = f
}

async function approveSelected() {
  try {
    const { data } = await axios.post('/payment-orders/bulk-approve', {
      ids: selectedIds.value
    })
    Swal.fire('Success', data.message, 'success')
    refetch()
    selectedIds.value = []
  } catch (e) {
    Swal.fire('Error', e.response?.data?.message ?? e.message, 'error')
  }
}

</script>

<template>
  <div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-xxl">
      <h1 class="d-flex align-items-center text-grey-900 fw-bolder fs-3 my-1 pb-4">Payment Orders</h1>

      <div class="card">
        <!-- Header -->
        <div class="card-header border-0 pt-6">
          <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
              <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
              <input v-model="searchQuery" type="text" class="form-control form-control-solid w-250px ps-12" placeholder="Search by Maid / notes ..." />
            </div>
          </div>
          <div class="card-toolbar">
            <div class="d-flex justify-content-end">
              <button class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i>Filter
              </button>
              <div id="kt-toolbar-filter" class="menu menu-sub menu-sub-dropdown w-300px w-md-350px" data-kt-menu="true">
                <div class="px-7 py-5"><div class="fs-4 text-gray-900 fw-bold">Filter Options</div></div>

    
                <div class="separator border-gray-200"></div>
                <div class="px-7 py-5">
                  <div class="mb-6"><label class="form-label fs-6 fw-semibold">Date From</label><input v-model="startDateFilter" type="date" class="form-control form-control-solid" /></div>
                  <div class="mb-6"><label class="form-label fs-6 fw-semibold">Date To</label><input v-model="endDateFilter" type="date" class="form-control form-control-solid" /></div>
                  <div class="mb-6">
                        <label class="form-label fs-6 fw-semibold">Status</label>
                        <select v-model="statusFilter" class="form-select form-select-solid">
                          <option value="">All</option>
                          <option :value="0">Pending</option>
                          <option :value="1">Approved</option>
                          <option :value="2">Rejected</option>
                        </select>
                      </div>

                  <div class="d-flex justify-content-end">
                    <button class="btn btn-light me-2" data-kt-menu-dismiss="true" @click="() => { filters.date_from.value=''; filters.date_to.value='' }">Reset</button>
                    <button class="btn btn-primary" data-kt-menu-dismiss="true" @click="refetch">Apply</button>
                  </div>
                </div>
              </div>
              <button class="btn btn-light-primary me-3" @click="exportToExcel">
                <i class="ki-duotone ki-exit-up fs-2"><span class="path1"></span><span class="path2"></span></i>
                Export
              </button>

              <div class="d-flex gap-3">
              <button class="btn btn-primary" @click="openModal">
                + Add
              </button>

              <button
                class="btn btn-primary"
                :disabled="!selectedIds.length"
                @click="approveSelected"
              >
                Approve
              </button>
            </div>


            </div>
          </div>
        </div>

        <div class="ps-8 pt-4"><span class="badge badge-light-secondary">Total: {{ total }}</span></div>

        <!-- Table -->
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
                  <th>Date</th>
                  <th>Maid</th>
                  <th>note</th>
                  <th>Amount</th>
                  <th>Txn</th>
                  <th>Method</th>
                  <th>Status</th>
                  <th>Attachment</th>
                  <th>Created_by </th>
                  <th>Updated_by </th>
                  <th>Created_at </th>
                  <th>Updated_at </th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody class="fw-semibold text-gray-600">
                <tr v-if="isLoading">
                  <td :colspan="9" class="text-center py-10"><div class="spinner-border text-primary me-2"></div>Loading…</td>
                </tr>
                <tr v-for="row in rows" :key="row.id">
                  <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                      <input type="checkbox" class="form-check-input" :value="String(row.id)" v-model="selectedIds" />
                    </div>
                  </td>
                  <td>{{ row.date }}</td>
                <td>
                  <a
                    :href="`/page/maid-finance/${encodeURIComponent(row.maid_name || row.maid_id || '')}`"
                  >
                    {{ row.maid_name ?? ('#' + (row.maid_id ?? '—')) }}
                  </a>
                </td>

                  <td>{{ row.note  }}</td>
  
                  <td>{{ Number(row.amount).toLocaleString() }}</td>
                  <td>{{ txLabel(row.transaction) }}</td>
                  <td>{{ methodLabel(row.payment_method) }}</td>
                  <td>{{ statusLabel(row.status) }}</td>
                  <td>
                    <a v-if="row.attachment" :href="row.attachment" target="_blank" class="text-primary text-decoration-underline">Open</a>
                    <span v-else>—</span>
                  </td>
                     <td>{{ row.created_by  }}</td>
                     <td>{{ row.updated_by  }}</td>
                     <td>{{ row.created_at }}</td>
                     <td>{{ row.updated_at  }}</td>

                  <td class="text-end">
                    <button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" @click="showEditModal(row)">
                      <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-5 flex-wrap gap-3">
              <div>
                <label class="me-2 fw-semibold">Page size:</label>
                <select v-model.number="pageSize" @change="handleSizeChange(parseInt($event.target.value))" class="form-select form-select-sm w-auto d-inline-block">
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

  <!-- Modal -->
  <div class="modal fade" id="kt_modal_crud" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title">{{ isEditing ? 'Edit Payment Order' : 'Add Payment Order' }}</h3>
          <button class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal" @click="destroyMaidSelect()">
            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
          </button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Date</label>
              <input v-model="form.date" type="date" class="form-control" />
            </div>

            <div class="col-md-8">
              <label class="form-label">Maid</label>
              <select id="maid_id_select" class="form-select w-100"></select>
              <small class="text-muted">Saved maid_id: {{ form.maid_id ?? '—' }}</small>
            </div>

            <div class="col-md-4">
              <label class="form-label">Amount</label>
              <input v-model="form.amount" type="number" min="0" step="0.01" class="form-control" />
            </div>
            <div class="col-md-4">
              <label class="form-label">Transaction</label>
              <select v-model="form.transaction" class="form-select">
                <option :value="3">Others</option>
                <option :value="2">Fine</option>
                <option :value="1">Visa</option>
                <option :value="0">Visa with fine</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Payment Method</label>
              <select v-model="form.payment_method" class="form-select">
                <option :value="0">Noqodi</option>
                <option :value="1">Card</option>
         
              </select>
            </div>

           
            <div class="col-md-8">
              <label class="form-label">Attachment (file)</label>
              <input type="file" class="form-control" @change="onFileChange"  />
              <div v-if="fileObj" class="form-text mt-1">
                Selected: <strong>{{ fileObj.name }}</strong> ({{ Math.ceil(fileObj.size/1024) }} KB)
              </div>
              <small class="text-muted">Uploaded to R2 on Save.</small>
            </div>

            <div class="col-12">
              <label class="form-label">Note</label>
              <input v-model="form.note" type="text" class="form-control" />
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal" @click="destroyMaidSelect()">Close</button>
          <button class="btn btn-primary" :disabled="modalLoading" @click="handleSubmit">
            <span v-if="modalLoading" class="spinner-border spinner-border-sm"></span>
            {{ isEditing ? 'Update' : 'Save' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* optional */
</style>
