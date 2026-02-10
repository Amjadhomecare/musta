<script setup>
import { ref, reactive, computed, watch } from 'vue'
import dayjs from 'dayjs'
import { useMutation } from '@tanstack/vue-query'
import { usePaginationQuery } from '@/composables/usePagination'
import AjaxSelect2 from '@/components/AjaxSelect2.vue'

/* ——— pagination helpers ——— */
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

/* ——— Filters (match controller names) ——— */
const filters = {
  date_from: ref(''),
  date_to  : ref(''),
  status   : ref(''),
  service  : ref(''),
  done     : ref(''),
  user     : ref(''),
  managment_approval: ref(''),
}

const {
  data, isLoading, refetch, currentPage, pageSize, searchQuery, total,
  handlePageChange, handleSizeChange, exportToExcel
} = usePaginationQuery({
  apiUrl        : '/apply-visas',
  queryKeyPrefix: 'visapro',
  filters,
})

const rows        = computed(() => data.value?.data ?? [])
const totalPages  = computed(() => Math.ceil((total.value || 0) / (pageSize.value || 10)))
const pageItems   = computed(() => totalPages.value <= 6
  ? Array.from({ length: totalPages.value }, (_, i) => i + 1)
  : buildPageItems(totalPages.value, currentPage.value, 1)
)

/* ——— Select-all ——— */
const selectedIds = ref([])
const allSelected = computed(() =>
  rows.value.length && rows.value.every(r => selectedIds.value.includes(String(r.id)))
)
function toggleSelectAll () {
  selectedIds.value = allSelected.value ? [] : rows.value.map(r => String(r.id))
}
watch(rows, () => (selectedIds.value = []))

/* ——— Modal + form ——— */
const isEditing    = ref(false)
const editId       = ref(null)
const modalLoading = ref(false)

// preload for AjaxSelect2 when editing
const maidPreload = ref(null) // { id, text }

/* Required by controller on store/update */
const form = reactive({
  date: dayjs().format('YYYY-MM-DD'),
  date_expiration: '',
  maid_id: '',
  service: 2,
  status: 0,
  managment_approval: 0,
  note: '',
  documents: [],
  new_comment: ''
})

function resetForm () {
  form.date = dayjs().format('YYYY-MM-DD')
  form.date_expiration = ''
  form.maid_id = ''
  form.service = 2
  form.status = 0
  form.managment_approval = 0
  form.note = ''
  form.documents = []
  form.new_comment = ''
  maidPreload.value = null
}

/* ——— Modal open/edit ——— */
function openModal () {
  isEditing.value = false
  editId.value = null
  resetForm()
  bootstrap.Modal.getOrCreateInstance('#kt_modal_crud').show()
}

function showEditModal (item) {
  isEditing.value = true
  editId.value = item.id
  form.date = item.date ?? ''
  form.date_expiration = item.date_expiration ?? ''
  form.maid_id = item.maid_id ?? ''
  form.service = item.service ?? ''
  form.status = item.status ?? ''
  form.managment_approval = item.managment_approval ?? ''
  form.note = item.note ?? ''
  form.documents = []
  form.new_comment = ''

  // Preload selection text in Select2
  if (item.maid_id) {
    maidPreload.value = {
      id: item.maid_id,
      text: item.maid_name || item.name || `#${item.maid_id}`
    }
  } else {
    maidPreload.value = null
  }

  bootstrap.Modal.getOrCreateInstance('#kt_modal_crud').show()
}

/* ——— Helpers ——— */
function getComments(row) {
  const c = row?.comments
  if (!c) return []
  if (Array.isArray(c)) return c
  if (typeof c === 'string') {
    try {
      const p = JSON.parse(c)
      return Array.isArray(p) ? p : []
    } catch { return [] }
  }
  return []
}

function getDaysRemaining(dateExpiration) {
  if (!dateExpiration) return null
  const today = dayjs()
  const expiry = dayjs(dateExpiration)
  const days = expiry.diff(today, 'day')
  return days
}

function getDaysRemainingText(days) {
  if (days === null) return '—'
  if (days < 0) return `Overdue by ${Math.abs(days)} day${Math.abs(days) !== 1 ? 's' : ''}`
  if (days === 0) return 'Expires today'
  return `${days} day${days !== 1 ? 's' : ''} left`
}

function getDaysRemainingClass(days) {
  if (days === null) return ''
  if (days <= 5) return 'badge-danger'
  if (days <= 15) return 'badge-warning'
  if (days <= 30) return 'badge-light-warning'
  return 'badge-success'
}

/* ——— build FormData for file upload ——— */
function toFormData (payload) {
  const fd = new FormData()
  Object.entries(payload).forEach(([k, v]) => {
    if (k === 'documents') {
      (v || []).forEach(f => fd.append('documents[]', f))
    } else if (v !== undefined && v !== null && v !== '') {
      fd.append(k, String(v))
    }
  })
  return fd
}

/* ——— Save (create / update) ——— */
const { mutate: saveItem } = useMutation({
  mutationFn: async () => {
    modalLoading.value = true
    const payload = {
      date: form.date,
      date_expiration: form.date_expiration,
      maid_id: form.maid_id,
      service: form.service,
      status: form.status,
      managment_approval: form.managment_approval,
      note: form.note,
      documents: form.documents,
      new_comment: form.new_comment,
      ...(isEditing.value ? { id: editId.value } : {}),
    }
    const fd = toFormData(payload)
    return isEditing.value
      ? axios.post('/apply-visas/update', fd)
      : axios.post('/apply-visas/store', fd)
  },
  onSuccess: (r) => {
    Swal.fire('Success', r.data?.message ?? 'Saved', 'success')
    refetch()
    bootstrap.Modal.getOrCreateInstance('#kt_modal_crud').hide()
  },
  onError: (e) => {
    const msg = e.response?.data?.message ?? (e.response?.data?.errors ? 'Validation failed' : e.message)
    Swal.fire('Error', msg, 'error')
  },
  onSettled: () => { modalLoading.value = false }
})

function handleSubmit () { saveItem() }

/* ——— file input handler ——— */
function onFilesSelected (e) {
  form.documents = Array.from(e.target.files || [])
}
</script>

<template>
  <div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-xxl">

      <h1 class="d-flex align-items-center text-grey-900 fw-bolder fs-3 my-1 pb-4">
        Visa Applications
      </h1>

      <div class="card">
        <!-- ========== Card header ========== -->
        <div class="card-header border-0 pt-6">
          <!-- search -->
          <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
              <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
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
              <button class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i>Filter
              </button>

              <!-- filter dropdown -->
              <div id="kt-toolbar-filter" class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                <div class="px-7 py-5">
                  <div class="fs-4 text-gray-900 fw-bold">Filter Options</div>
                </div>
                <div class="separator border-gray-200"></div>
                <div class="px-7 py-5">
                  <div class="mb-6">
                    <label class="form-label fs-5 fw-semibold mb-2">Date From</label>
                    <input type="date" class="form-control form-control-solid" v-model="filters.date_from.value">
                  </div>
                  <div class="mb-10">
                    <label class="form-label fs-5 fw-semibold mb-2">Date To</label>
                    <input type="date" class="form-control form-control-solid" v-model="filters.date_to.value">
                  </div>

                  <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold mb-2">Status</label>
                    <select class="form-select form-select-solid" v-model="filters.status.value">
                      <option value="">All</option>
                      <option value="0">Created</option>
                      <option value="1">Pending</option>
                      <option value="2">Missing document</option>
                      <option value="3">Contract done</option>
                      <option value="4">Labor insurance done</option>
                      <option value="5">Work permit done</option>
                      <option value="6">Entry permit done</option>
                      <option value="7">Change status done</option>
                      <option value="8">Medical done</option>
                      <option value="9">Eid done</option>
                      <option value="10">Visa stamp done</option>
                      <option value="11">Rejected</option>
                      <option value="12">Cancelation Done</option>
                      <option value="13">Renewal Done</option>
                      <option value="14">Absconding Done</option>
                    </select>
                  </div>

                  <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold mb-2">Service</label>
                    <select class="form-select form-select-solid" v-model="filters.service.value">
                      <option value="">All</option>
                      <option value="2">New visa</option>
                      <option value="0">Visa renewal</option>
                      <option value="3">Cancellation</option>
                      <option value="4">Absconding</option>
                      <option value="5">Other</option>
                    </select>
                  </div>

                  <div class="mb-10">
                    <label class="form-label fs-6 fw-semibold mb-2">Management Approval</label>
                    <select class="form-select form-select-solid" v-model="filters.managment_approval.value">
                      <option value="">All</option>
                      <option value="0">Pending</option>
                      <option value="1">Approved</option>
                      <option value="2">Urgent</option>
                    </select>
                  </div>

                  <div class="form-check form-switch form-check-custom form-check-solid">
                    <input class="form-check-input" type="checkbox" id="flexSwitchDone" v-model="filters.done.value"/>
                    <label class="form-check-label" for="flexSwitchDone">
                      Remove done applications
                    </label>
                  </div>

                  <div class="form-check form-switch form-check-custom form-check-solid">
                    <input class="form-check-input" type="checkbox" id="flexSwitchUsers" v-model="filters.user.value"/>
                    <label class="form-check-label" for="flexSwitchUsers">
                      Filter online users
                    </label>
                  </div>

                  <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" data-kt-menu-dismiss="true" @click="refetch">Apply</button>
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
                      <input type="checkbox" class="form-check-input" :checked="allSelected" @change="toggleSelectAll" />
                    </div>
                  </th>
                  <th>Date</th>
                  <th>Expiry Date</th>
                  <th>Maid</th>
                  <th>Service</th>
                  <th>Status</th>
                  <th>Mgmt</th>
                  <th>Note</th>
                  <th>Comments</th>
                  <th>Attachment</th>
                  <th>Created by</th>
                  <th>Updated by</th>
                  <th class="text-end">Actions</th>
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
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                      <input type="checkbox" class="form-check-input" :value="String(row.id)" v-model="selectedIds" />
                    </div>
                  </td>
                  <td>{{ row.date ?? '—' }}</td>
                  <td>
                    <span class="badge" :class="getDaysRemainingClass(getDaysRemaining(row.date_expiration))">
                      {{ getDaysRemainingText(getDaysRemaining(row.date_expiration)) }}
                    </span>
                  </td>
                  <td>
                    <a :href="`/maid-doc-expiry/${encodeURIComponent(row.maid_id)}`">
                      {{ row.maid_name ?? ('#' + (row.maid_id ?? '—')) }}
                    </a>
                  </td>
                  <td>
                    <span class="badge badge-light">
                      {{ {0:'Visa renewal',2:'New visa',3:'Cancellation',4:'Absconding',5:'Other'}[row.service] ?? '—' }}
                    </span>
                  </td>
                  <td>
                    <span class="badge" :class="{
                      'badge-light'           : row.status==0,
                      'badge-light-warning'   : [1,2,3,4,5,6,7].includes(row.status),
                      'badge-light-success'   : [8,9,10,12,13,14].includes(row.status),
                      'badge-light-danger'    : row.status==11
                    }">
                      {{
                        {
                          0:'Created',
                          1:'Pending',
                          2:'Missing document',
                          3:'Contract done',
                          4:'Labor insurance done',
                          5:'Work permit done',
                          6:'Entry permit done',
                          7:'Change status done',
                          8:'Medical done',
                          9:'Eid done',
                          10:'Visa stamp done',
                          11:'Rejected',
                          12:'Canceled',
                          13:'Renewaled',
                          14:'Absconding done',
                        }[row.status] ?? '—'
                      }}
                    </span>
                  </td>
                  <td>
                    <span class="badge" :class="row.managment_approval==1 ? 'badge-light-success' : 'badge-light-warning'">
                      {{ row.managment_approval==1 ? 'Approved' : 'Pending' }}
                    </span>
                  </td>
                  <td>{{ row.note ?? '—' }}</td>

                  <!-- Stacked comments (parsed safely) -->
                  <td>
                    <ul class="list-unstyled mb-0">
                      <li v-for="(c, i) in getComments(row)" :key="i">
                        <small>
                          <strong>{{ c.by }}</strong>: {{ c.text }}
                          <em>({{ c.at ? dayjs(c.at).format('YYYY-MM-DD HH:mm') : '' }})</em>
                        </small>
                      </li>
                      <li v-if="getComments(row).length === 0">—</li>
                    </ul>
                  </td>

                  <td>{{ row.document_count ?? '—' }}</td>
                  <td>{{ row.created_by ?? '—' }}</td>
                  <td>{{ row.updated_by ?? '—' }}</td>
                  <td class="text-end">
                    <button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" @click="showEditModal(row)">
                      <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- pagination -->
            <div class="d-flex justify-content-between align-items-center mt-5 flex-wrap gap-3">
              <div>
                <label class="me-2 fw-semibold">Page size:</label>
                <select v-model="pageSize" @change="handleSizeChange(parseInt($event.target.value))"
                        class="form-select form-select-sm w-auto d-inline-block">
                  <option value="10">10</option><option value="25">25</option>
                  <option value="50">50</option><option value="100">100</option>
                </select>
              </div>

              <ul class="pagination mb-0">
                <li :class="['page-item previous', { disabled: currentPage === 1 }]">
                  <a href="javascript:;" class="page-link" @click="handlePageChange(Math.max(1, currentPage - 1))">
                    <i class="previous"></i>
                  </a>
                </li>

                <li v-for="it in pageItems" :key="`p-${it}`" :class="['page-item', { active: it === currentPage, disabled: it === '...' }]">
                  <a v-if="it !== '...'" href="javascript:;" class="page-link" @click="handlePageChange(it)">{{ it }}</a>
                  <a v-else href="javascript:;" class="page-link">…</a>
                </li>

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
          <h3 class="modal-title">{{ isEditing ? 'Edit Visa Application' : 'Add Visa Application' }}</h3>
          <button class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal">
            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
          </button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Date</label>
              <input v-model="form.date" type="date" class="form-control" />
            </div>

            <div class="col-md-4">
              <label class="form-label">Expiration Date</label>
              <input v-model="form.date_expiration" type="date" class="form-control" placeholder="Last day allowed to stay" />
            </div>

            <div class="col-md-4">
              <label class="form-label">Maid</label>

              <!-- NEW: Reusable Select2 wrapper -->
              <AjaxSelect2
                v-model="form.maid_id"
                class="form-control"
                url="/all/maids"
                placeholder="Select maid"
                dropdownParent="#kt_modal_crud"
                :preloadOption="maidPreload"
                :mapResults="(data, params, perPage) => ({
                  // Support your current API shape: items -> { system_id, id, text }
                  results: (data?.items || []).map(i => ({
                    id: i.system_id ?? i.id, // prefer system_id if present
                    text: i.text
                  })),
                  pagination: { more: (params.page || 1) * 30 < (data?.total_count || 0) }
                })"
              />

              <small class="text-muted">{{ form.maid_id }}</small>
            </div>

            <div class="col-md-4">
              <label class="form-label">Service</label>
              <select v-model="form.service" class="form-select">
                <option value="">Choose…</option>
                <option value="2">New visa</option>
                <option value="0">Visa renewal</option>
                <option value="3">Cancellation</option>
                <option value="4">Absconding</option>
                <option value="5">Other</option>
              </select>
            </div>

            <div class="col-md-4" v-if="isEditing">
              <label class="form-label">Status</label>
              <select v-model="form.status" class="form-select">
                <option value="">Choose…</option>
                <option value="0">Created</option>
                <option value="1">Submitted</option>
                <option value="2">Missing document</option>
                <option value="3">Contract done</option>
                <option value="4">Labor insurance done</option>
                <option value="5">Work permit done</option>
                <option value="6">Entry permit done</option>
                <option value="7">Change status done</option>
                <option value="8">Medical done</option>
                <option value="9">Eid done</option>
                <option value="10">Visa stamp done</option>
                <option value="11">Rejected</option>
                <option value="12">Cancellation Done</option>
                <option value="13">Renewal Done</option>
                <option value="14">Absconding Done</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Management Approval</label>
              <select v-model="form.managment_approval" class="form-select">
                <option value="">Choose…</option>
                <option value="0">Pending</option>
                <option value="1">Approved</option>
              </select>
            </div>

            <div class="col-md-12">
              <label class="form-label">Note</label>
              <input v-model="form.note" type="text" class="form-control" placeholder="Example : 1-passport , 2- photo , 3-basic salary to appy, els..." />
            </div>

            <div class="col-md-12">
              <label class="form-label">New Comment</label>
              <textarea v-model="form.new_comment" class="form-control" rows="2" placeholder="Add a new comment..."></textarea>
            </div>

            <div class="col-md-12">
              <label class="form-label">Documents</label>
              <input type="file" class="form-control" multiple @change="onFilesSelected" />
              <div class="form-text"> upload multiple files</div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
          <button class="btn btn-primary" :disabled="modalLoading" @click="handleSubmit">
            <span v-if="modalLoading" class="spinner-border spinner-border-sm"></span>
            {{ isEditing ? 'Update' : 'Save' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
