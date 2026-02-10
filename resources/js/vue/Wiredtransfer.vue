<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { useMutation, useQuery }        from '@tanstack/vue-query'
import { usePaginationQuery } from '@/composables/usePagination'
import AjaxSelect2 from '@/components/AjaxSelect2.vue'  



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
const filters = { start_date: ref(''), end_date: ref('')  , status: ref('')}
const startDateFilter = computed({
  get: () => filters.start_date.value, set: v => (filters.start_date.value = v)
})
const endDateFilter = computed({
  get: () => filters.end_date.value,   set: v => (filters.end_date.value = v)
})
const statusFilter = computed({
  get: () => filters.status.value, set: v => (filters.status.value = v)
})

/* ––– Pagination query ––– */
const {
  data, isLoading, refetch, currentPage, pageSize, searchQuery, total,
  handlePageChange, handleSizeChange, exportToExcel
} = usePaginationQuery({
  apiUrl        : '/api/wiredtransfer',
  queryKeyPrefix: 'wiredtransfer',
  filters
})
const totalPages = computed(() => Math.ceil(total.value / pageSize.value))

/* ––– Select‑all ––– */
const selectedIds = ref([])
const rows        = computed(() => data.value?.data ?? [])

const allSelected = computed(() =>
  rows.value.length && rows.value.every(r => selectedIds.value.includes(String(r.id)))
)
function toggleSelectAll () {
  selectedIds.value = allSelected.value ? [] : rows.value.map(r => String(r.id))
}
watch(rows, () => (selectedIds.value = []))

/* ––– Customer search ––– */
const customerSearch = ref('')
const { data: customers } = useQuery({
  queryKey: ['customers-search', customerSearch],
  queryFn: async () => {
    if (!customerSearch.value) return []
    const res = await axios.get('/api/customers', { params: { search: customerSearch.value } })
    return res.data.data || res.data || []
  },
  enabled: computed(() => customerSearch.value.length > 0)
})

/* ––– Modal + form ––– */
const form = reactive({ 
  customer_id: null, 
  amount_value: '', 
  status: 0,
  note: '',
  file: null,
  url: '' // current file URL for edit mode
})
const isEditing = ref(false)
const editId = ref(null)
const modalLoading = ref(false)
const fileInput = ref(null)
const filePreview = ref(null)

function resetForm() { 
  Object.assign(form, { 
    customer_id: null, 
    amount_value: '', 
    status: 0, 
    note: '',
    file: null,
    url: ''
  })
  filePreview.value = null
  if (fileInput.value) fileInput.value.value = ''
}

function openModal() {
  isEditing.value = false; editId.value = null; resetForm()
  bootstrap.Modal.getOrCreateInstance('#kt_modal_crud').show()
}

function showEditModal(item) {
  isEditing.value = true; editId.value = item.id;
  Object.assign(form, { 
    customer_id: item.customer_id,
    amount_value: item.amount_value,
    status: item.status,
    note: item.note || '',
    url: item.url || '',
    file: null
  })
  filePreview.value = null
  bootstrap.Modal.getOrCreateInstance('#kt_modal_crud').show()
}

function handleFileChange(e) {
  const file = e.target.files?.[0]
  form.file = file || null
  
  // Preview if image
  if (file && file.type.startsWith('image/')) {
    const reader = new FileReader()
    reader.onload = (e) => { filePreview.value = e.target.result }
    reader.readAsDataURL(file)
  } else {
    filePreview.value = null
  }
}

/* ––– Save (create / update) ––– */
const { mutate: saveItem } = useMutation({
  mutationFn: p => {
    const formData = new FormData()
    formData.append('customer_id', p.customer_id)
    formData.append('amount_value', p.amount_value)
    formData.append('status', p.status)
    if (p.note) formData.append('note', p.note)
    if (p.file) formData.append('file', p.file)

    return editId.value
      ? axios.post(`/api/wiredtransfer/${editId.value}`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
          params: { _method: 'PUT' } // Laravel method spoofing
        })
      : axios.post('/api/wiredtransfer', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
  },
  onMutate  : () => (modalLoading.value = true),
  onSuccess : r => {
    Swal.fire('Success', r.data.message ?? 'Saved', 'success')
    refetch(); bootstrap.Modal.getOrCreateInstance('#kt_modal_crud').hide()
  },
  onError   : e => Swal.fire('Error', e.response?.data?.message ?? e.message, 'error'),
  onSettled : () => (modalLoading.value = false)
})

function handleSubmit() { 
  if (!form.customer_id) {
    Swal.fire('Error', 'Please select a customer', 'error')
    return
  }
  saveItem({ ...form }) 
}

/* ––– Delete ––– */
const { mutate: deleteItem } = useMutation({
  mutationFn: id => axios.delete(`/api/wiredtransfer/${id}`),
  onSuccess: r => {
    Swal.fire('Deleted', r.data.message ?? 'Record deleted', 'success')
    refetch()
  },
  onError: e => Swal.fire('Error', e.response?.data?.message ?? e.message, 'error')
})

function confirmDelete(id) {
  Swal.fire({
    title: 'Are you sure?',
    text: 'This will delete the wired transfer and its file',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      deleteItem(id)
    }
  })
}

// Status badge helper
function getStatusBadge(status) {
  const badges = {
    0: 'badge-light-warning',  // Pending
    1: 'badge-light-success',  // Completed
    2: 'badge-light-info',     // Unknown
    3: 'badge-light-danger'    // Not Found
  }
  return badges[status] || 'badge-light-secondary'
}

</script>

<template>
  <div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-xxl">

      <h1 class="d-flex align-items-center text-grey-900 fw-bolder fs-3 my-1 pb-4">
        WiredTransfer
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
                    <label class="form-label fs-5 fw-semibold mb-3">Status:</label>
                    <select
                      v-model="statusFilter"
                      class="form-select form-select-solid fw-bold"
                      data-placeholder="Select option"
                    >
                      <option value="">All</option>
                      <option value="0">Pending</option>
                      <option value="1">Completed</option>
                      <option value="2">Unknown</option>
                      <option value="3">Not Found</option>
                    </select>
                  </div>
                  <div class="d-flex justify-content-end">
                    <button
                      class="btn btn-primary"
                      data-kt-menu-dismiss="true"
                      @click="refetch"
                    >Apply</button>
                  </div>
                </div>

              </div>

              <!-- export + add -->
              <button class="btn btn-light-primary me-3" @click="exportToExcel">
                <i class="ki-duotone ki-exit-up fs-2"><span class="path1"></span><span class="path2"></span></i>
                Export
              </button>
              <button class="btn btn-primary" @click="openModal">+ Add</button>
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
                  <th>Customer</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>File</th>
                  <th>Note</th>
                  <th>Created At</th>
                  <th>Created By</th>
                  <th>Updated By</th>
                  <th>Updated At</th>
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
                  <td><a :href="'/customer/soa/' + row.customer_name">{{ row.customer_name }}</a></td>
                  <td class="fw-bold">{{ parseFloat(row.amount_value).toFixed(2) }} AED</td>
                  <td>
                    <span :class="['badge', getStatusBadge(row.status)]">
                      {{ row.status_label }}
                    </span>
                  </td>
                  <td>
                    <a v-if="row.url" :href="row.url" target="_blank" class="text-primary">
                      <i class="ki-duotone ki-file fs-2"><span class="path1"></span><span class="path2"></span></i>
                      View File
                    </a>
                    <span v-else class="text-muted">—</span>
                  </td>
                  <td>{{ row.note ?? '—' }}</td>
                  <td>{{ row.created_at }}</td>
                  <td>{{ row.updated_at }}</td>
                  <td>{{ row.created_by }}</td>
                  <td>{{ row.updated_by }}</td>
                  <td class="text-end">
                    <button
                      class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                      @click="showEditModal(row)"
                      title="Edit"
                    >
                      <i class="ki-duotone ki-pencil fs-2">
                        <span class="path1"></span><span class="path2"></span>
                      </i>
                    </button>
                    <button
                      class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm"
                      @click="confirmDelete(row.id)"
                      title="Delete"
                    >
                      <i class="ki-duotone ki-trash fs-2">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span>
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

  <!-- ========== Modal ========== -->
  <div class="modal fade" id="kt_modal_crud" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title">{{ isEditing ? 'Edit' : 'Add' }} Wired Transfer</h3>
          <button class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal">
            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
          </button>
        </div>

        <div class="modal-body">
          <div class="row g-4">
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
            
            <div class="col-md-6">
              <label class="form-label required">Amount (AED)</label>
              <input 
                v-model="form.amount_value" 
                type="number" 
                step="0.01"
                min="0.01"
                class="form-control" 
                placeholder="0.00"
                required
              />
            </div>

            <div v-if="isEditing" class="col-md-6">
              <label class="form-label">Status</label>
              <select v-model="form.status" class="form-select">
                <option :value="0">Pending</option>
                <option :value="1">Completed</option>
                <option :value="2">Unknown</option>
                <option :value="3">Not Found</option>
              </select>
              <div class="form-text text-warning">Only accountant can update status</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Upload File</label>
              <input 
                ref="fileInput"
                type="file" 
                class="form-control" 
                accept="image/*,.pdf,.doc,.docx"
                @change="handleFileChange"
              />
              <div class="form-text">JPG, PNG, PDF, DOC (Max 10MB)</div>
            </div>

            <div v-if="form.url && !filePreview" class="col-12">
              <label class="form-label">Current File</label>
              <div>
                <a :href="form.url" target="_blank" class="btn btn-sm btn-light-primary">
                  <i class="ki-duotone ki-file"><span class="path1"></span><span class="path2"></span></i>
                  View Current File
                </a>
              </div>
            </div>

            <div v-if="filePreview" class="col-12">
              <label class="form-label">File Preview</label>
              <div>
                <img :src="filePreview" class="img-thumbnail" style="max-height: 200px" />
              </div>
            </div>

            <div class="col-12">
              <label class="form-label">Note</label>
              <textarea 
                v-model="form.note" 
                class="form-control" 
                rows="3"
                placeholder="Add any notes or comments"
              ></textarea>
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
</template>

<style scoped>
/* Page‑specific tweaks (optional) */
</style>