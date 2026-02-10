<script setup>
import { ref, reactive, computed, watch, nextTick, h, defineComponent, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { useMutation } from '@tanstack/vue-query'
import { usePaginationQuery } from '@/composables/usePagination'

/* ---------------------------------------------------
   Axios defaults
--------------------------------------------------- */
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
if (token) axios.defaults.headers.common['X-CSRF-TOKEN'] = token

/* ---------------------------------------------------
   Select2 (AJAX to /add/new/ledger)
--------------------------------------------------- */
const Select2Ledger = defineComponent({
  name: 'Select2Ledger',
  props: {
    modelValue: { type: Object, default: null }, // { system_id, text }
    placeholder: { type: String, default: 'Search ledger...' },
    dropdownParent: { type: String, default: null },
    size: { type: String, default: 'sm' }
  },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const el = ref(null)
    let $sel = null

    function init() {
      if (!el.value || !window.$) return
      $sel = window.$(el.value)
      if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy')

      $sel.select2({
        dropdownParent: props.dropdownParent ? window.$(props.dropdownParent) : undefined,
        placeholder: props.placeholder,
        allowClear: true,
        width: '100%',
        ajax: {
          url: '/add/new/ledger',
          delay: 250,
          dataType: 'json',
          data: params => ({ search: params.term, page: params.page || 1 }),
          processResults: ({ items = [] }) => ({
            results: items.map(i => ({
              id: i.system_id,
              text: i.text,
              system_id: i.system_id,
              raw: i
            }))
          }),
          cache: true
        },
        templateResult: r => (r.loading ? r.text : r.text),
        templateSelection: r => r.text || r.id,
        escapeMarkup: m => m
      })
      .on('select2:select', e => {
        const d = e.params.data
        emit('update:modelValue', { system_id: d.system_id, text: d.text })
      })
      .on('select2:clear', () => emit('update:modelValue', null))

      if (props.modelValue?.system_id) {
        const opt = new Option(props.modelValue.text, props.modelValue.system_id, true, true)
        $sel.append(opt).trigger('change')
      }
    }

    watch(() => props.modelValue, (nv) => {
      if (!$sel) return
      if (!nv) return $sel.val(null).trigger('change')
      const exists = $sel.find(`option[value="${nv.system_id}"]`).length
      if (!exists) $sel.append(new Option(nv.text, nv.system_id, true, true))
      $sel.val(String(nv.system_id)).trigger('change')
    }, { deep: true })

    watch(() => el.value, async v => { if (v) { await nextTick(); init() } })
    onBeforeUnmount(() => { if ($sel && $sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy') })

    return () => h('select', { ref: el, class: `form-select form-select-${props.size}` })
  }
})

/* ---------------------------------------------------
   Pagination + list
--------------------------------------------------- */
const filters = { start_date: ref(''), end_date: ref('') }

const {
  data, isLoading, refetch, currentPage, pageSize, searchQuery, total,
  handlePageChange, handleSizeChange, exportToExcel
} = usePaginationQuery({
  apiUrl        : '/accounting/recursions',
  queryKeyPrefix: 'accounting_recursions',
  filters
})

const rows       = computed(() => data.value?.data ?? [])
const totalPages = computed(() => Math.ceil((total.value || 0) / (pageSize.value || 10)))

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
const pageItems = computed(() => {
  const tp = totalPages.value
  const cp = currentPage.value
  if (tp <= 6) return Array.from({ length: tp }, (_, i) => i + 1)
  return buildPageItems(tp, cp, 1)
})

/* ---------------------------------------------------
   Grouping by name
--------------------------------------------------- */
const groups = computed(() => {
  const map = new Map()
  for (const r of rows.value) {
    const key = (r.name || '—').trim()
    if (!map.has(key)) map.set(key, [])
    map.get(key).push(r)
  }
  for (const [k, list] of map.entries()) {
    list.sort((a,b) => (new Date(b.created_at||0)) - (new Date(a.created_at||0)))
  }
  return Array.from(map.entries()).map(([name, items]) => {
    const debit  = items.filter(i => (i.post_type||'').toLowerCase()==='debit' ).reduce((s,i)=>s+Number(i.amount||0),0)
    const credit = items.filter(i => (i.post_type||'').toLowerCase()==='credit').reduce((s,i)=>s+Number(i.amount||0),0)
    return { name, items, count: items.length, debit, credit }
  })
})

/* ---------------------------------------------------
   Modal (create/edit group)
   KEY FIXES:
   - Carry each line's `id`
   - Track originalIds for delete_ids
--------------------------------------------------- */
const modalEl  = ref(null)
const modalKey = ref(0)

const isEdit    = ref(false)
const originalIds = ref([])          // <-- IDs that existed before editing (for deletions)

const form = reactive({
  name: '',
  recursion: 1,
  recursion_number: 0,
  start_date: '',
  note: ''
})

const lines = ref([
  // { _k, id?, ledger:{system_id,text}, post_type, amount, note }
  { _k: Date.now() + '-0', id: null, ledger: null, post_type: 'debit',  amount: 0, note: '' },
  { _k: Date.now() + '-1', id: null, ledger: null, post_type: 'credit', amount: 0, note: '' }
])

function addLine () {
  lines.value.push({ _k: Date.now() + '-' + Math.random().toString(36).slice(2,7), id: null, ledger: null, post_type: 'debit', amount: 0, note: '' })
}
function removeLine (idx) {
  lines.value.splice(idx, 1)
  if (!lines.value.length) addLine()
}

const totalDebit  = computed(() =>
  lines.value.filter(l => l.post_type === 'debit').reduce((s, l) => s + Number(l.amount || 0), 0)
)
const totalCredit = computed(() =>
  lines.value.filter(l => l.post_type === 'credit').reduce((s, l) => s + Number(l.amount || 0), 0)
)
const isBalanced = computed(() => Math.abs(totalDebit.value - totalCredit.value) < 0.00001)

const modalLoading = ref(false)

function resetForm () {
  isEdit.value = false
  originalIds.value = []
  form.name = ''
  form.recursion = 1
  form.recursion_number = 0
  form.start_date = ''
  form.note = ''
  lines.value = [
    { _k: Date.now() + '-0', id: null, ledger: null, post_type: 'debit',  amount: 0, note: '' },
    { _k: Date.now() + '-1', id: null, ledger: null, post_type: 'credit', amount: 0, note: '' }
  ]
}

async function openModalForCreate () {
  resetForm()
  modalKey.value++
  await nextTick()
  bootstrap.Modal.getOrCreateInstance(modalEl.value).show()
}

/** Build edit form from a GROUP (all rows with same name) */
async function openModalForEditGroup(group) {
  resetForm()
  isEdit.value = true

  // Header fields from the first row
  const first = group.items[0] || {}
  form.name = group.name
  form.recursion = Number(first.recursion ?? 1)
  form.recursion_number = Number(first.recursion_number ?? 0)
  form.start_date = first.start_date ?? ''
  form.note = first.note ?? ''

  // ORIGINAL IDS present before editing
  originalIds.value = group.items.map(i => i.id)

  // Lines from all rows — IMPORTANT: keep `id`
  lines.value = group.items.map((it, idx) => ({
    _k: `${group.name}-${it.id ?? idx}`,
    id: it.id ?? null,                                              // <--- carry id
    ledger: (it.ledger_ref_id || it.ledger_id)
      ? { system_id: it.ledger_ref_id ?? it.ledger_id, text: it.ledger_name ?? `#${it.ledger_ref_id ?? it.ledger_id}` }
      : null,
    post_type: it.post_type || 'debit',
    amount: Number(it.amount || 0),
    note: it.note || ''
  }))

  if (lines.value.length < 2) {
    lines.value.push({ _k: Date.now() + '-x', id: null, ledger: null, post_type: 'credit', amount: 0, note: '' })
  }

  modalKey.value++
  await nextTick()
  bootstrap.Modal.getOrCreateInstance(modalEl.value).show()
}

/* ---------------------------------------------------
   Save (Create/Update)
   UPDATE sends: lines with id (when editing), plus delete_ids
--------------------------------------------------- */
const { mutate: saveRecursions } = useMutation({
  mutationFn: ({ isEdit, payload }) => {
    if (isEdit) return axios.post('/accounting/recursions/update', payload)
    return axios.post('/accounting/recursions', payload)
  },
  onMutate  : () => (modalLoading.value = true),
  onSuccess : (r) => {
    Swal.fire('Success', r.data?.message ?? (isEdit.value ? 'Updated' : 'Saved'), 'success')
    refetch()
    bootstrap.Modal.getOrCreateInstance(modalEl.value).hide()
  },
  onError   : (e) => {
    const msg = e.response?.data?.message || (e.response?.data?.errors ? Object.values(e.response.data.errors).flat().join('\n') : e.message)
    Swal.fire('Error', msg, 'error')
  },
  onSettled : () => (modalLoading.value = false),
})

function handleSubmit () {
  if (!form.name)        return Swal.fire('Missing name', 'Please enter a name.', 'warning')
  if (!form.start_date)  return Swal.fire('Missing start date', 'Please choose a start date.', 'warning')
  if (lines.value.length < 2) return Swal.fire('Need lines', 'Add at least one debit and one credit line.', 'warning')
  if (!isBalanced.value) return Swal.fire('Not balanced', 'Total debit must equal total credit.', 'warning')
  if (lines.value.some(l => !l.ledger?.system_id)) return Swal.fire('Missing ledger', 'Select a ledger for each line.', 'warning')

  // Build payload lines (KEEP id when present)
  const outLines = lines.value.map(l => ({
    id        : l.id ?? undefined,                 // <--- keep id for updates
    ledger_id : l.ledger.system_id,
    post_type : l.post_type,
    amount    : Number(l.amount || 0),
    note      : l.note || ''
  }))

  // Compute delete_ids = ids that existed originally but are now missing
  const currentIds = new Set(outLines.filter(x => x.id).map(x => Number(x.id)))
  const deleteIds = originalIds.value.filter(id => !currentIds.has(Number(id)))

  const payload = {
    name: form.name,
    recursion: Number(form.recursion),
    recursion_number: Number(form.recursion_number),
    start_date: form.start_date || null,
    note: form.note || '',
    lines: outLines,
    delete_ids: deleteIds.length ? deleteIds : undefined
  }

  saveRecursions({ isEdit: isEdit.value, payload })
}

/* ---------------------------------------------------
   Delete by name (for group action; optional)
--------------------------------------------------- */
const { mutate: destroyByName, isLoading: deletingName } = useMutation({
  mutationFn: async ({ name, ids }) => {
    try {
      return await axios.delete('/accounting/recursions', { data: { name } })
    } catch (e) {
      for (const id of ids) await axios.delete(`/accounting/recursions/${id}`)
      return { data: { message: 'Deleted group via fallback.' } }
    }
  },
  onSuccess : (r) => { Swal.fire('Deleted', r.data?.message ?? 'Group deleted.', 'success'); refetch() },
  onError   : (e) => { Swal.fire('Error', e.response?.data?.message || e.message, 'error') }
})

async function confirmDeleteGroup(group) {
  const res = await Swal.fire({
    title: `Delete all "${group.name}"?`,
    text: `This will remove ${group.count} record(s) with the name "${group.name}".`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete all'
  })
  if (res.isConfirmed) destroyByName({ name: group.name, ids: group.items.map(x => x.id) })
}

/* ---------------------------------------------------
   Helpers
--------------------------------------------------- */
function formatRecursion(r) { return Number(r) === 2 ? 'Weekly' : 'Monthly' }
function fmtDate(d) { if (!d) return '—'; try { return new Date(d).toLocaleDateString() } catch { return d } }
function money(n) { const v = Number(n || 0); return v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }
</script>

<template>
  <div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-xxl">

      <h1 class="d-flex align-items-center text-grey-900 fw-bolder fs-3 my-1 pb-4">
        RecursionsJv
      </h1>

      <div class="card">
        <div class="card-header border-0 pt-6">
          <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
              <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
              <input v-model="searchQuery" type="text" class="form-control form-control-solid w-250px ps-12" placeholder="Type to Search ..." />
            </div>
          </div>
          <div class="card-toolbar">
            <div class="d-flex justify-content-end">
              <button class="btn btn-light-primary me-3" @click="exportToExcel">
                <i class="ki-outline ki-exit-up fs-2 me-2"></i> Export
              </button>
              <button class="btn btn-primary" @click="openModalForCreate">
                <i class="ki-outline ki-plus fs-2 me-2"></i> Add
              </button>
            </div>
          </div>
        </div>

        <div class="ps-8 pt-4">
          <span class="badge badge-light-secondary">Total: {{ total }}</span>
        </div>

        <div class="card-body pt-0">
          <div class="table-responsive">
            <!-- GROUPED BY NAME -->
            <table class="table align-middle table-row-dashed fs-6 gy-5">
              <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                  <th class="w-25">Name (grouped)</th>
                  <th class="text-end">Items</th>
                  <th class="text-end">Total Debit</th>
                  <th class="text-end">Total Credit</th>
                  <th class="text-end">Group Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="isLoading">
                  <td :colspan="5" class="text-center py-10">
                    <div class="spinner-border text-primary me-2"></div>
                    Loading data...
                  </td>
                </tr>

                <template v-for="g in groups" :key="`grp-${g.name}`">
                  <tr class="bg-light">
                    <td class="fw-bold">{{ g.name }}</td>
                    <td class="text-end"><span class="badge badge-light-primary">{{ g.count }}</span></td>
                    <td class="text-end"><span class="badge badge-light-success">{{ money(g.debit) }}</span></td>
                    <td class="text-end"><span class="badge badge-light-danger">{{ money(g.credit) }}</span></td>
                    <td class="text-end">
                      <div class="btn-group">
                        <button class="btn btn-sm btn-light-primary" @click="openModalForEditGroup(g)">
                          <i class="ki-outline ki-pencil fs-4 me-1"></i> Edit group
                        </button>
                        <button class="btn btn-sm btn-light-danger" :disabled="deletingName" @click="confirmDeleteGroup(g)">
                          <i class="ki-outline ki-trash fs-4 me-1"></i> Delete by name
                        </button>
                      </div>
                    </td>
                  </tr>

                  <!-- Items inside the group -->
                  <tr>
                    <td :colspan="5" class="p-0">
                      <div class="table-responsive">
                        <table class="table table-sm align-middle mb-6">
                          <thead>
                            <tr class="text-gray-600 fw-bold fs-7 text-uppercase">
                              <th>Recursion</th>
                              <th>Times</th>
                              <th>Start date</th>
                              <th>Ledger</th>
                              <th>Post type</th>
                              <th class="text-end">Amount</th>
                              <th>Note</th>
                              <th>Created</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="row in g.items" :key="row.id">
                              <td>{{ formatRecursion(row.recursion) }}</td>
                              <td>{{ Number(row.recursion_number ?? 0) }}</td>
                              <td>{{ fmtDate(row.start_date) }}</td>
                              <td>{{ row.ledger_name ?? (row.ledger_ref_id ? ('#' + row.ledger_ref_id) : '—') }}</td>
                              <td>
                                <span :class="['badge', (row.post_type || 'debit') === 'debit' ? 'badge-light-success' : 'badge-light-danger']">
                                  {{ (row.post_type || 'debit').toUpperCase() }}
                                </span>
                              </td>
                              <td class="text-end">{{ money(row.amount) }}</td>
                              <td>{{ row.note ?? '—' }}</td>
                              <td>{{ fmtDate(row.created_at) }}</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-5 flex-wrap gap-3">
              <div>
                <label class="me-2 fw-semibold">Page size:</label>
                <select v-model="pageSize" @change="handleSizeChange(parseInt(($event.target).value))" class="form-select form-select-sm w-auto d-inline-block">
                  <option value="10">10</option><option value="25">25</option>
                  <option value="50">50</option><option value="100">100</option>
                </select>
              </div>

              <ul class="pagination mb-0">
                <li :class="['page-item previous', { disabled: currentPage === 1 }]">
                  <a href="javascript:;" class="page-link" @click="handlePageChange(Math.max(1, currentPage - 1))">
                    <i class="ki-outline ki-arrow-left fs-3"></i>
                  </a>
                </li>
                <li v-for="it in pageItems" :key="`p-${it}`" :class="['page-item', { active: it === currentPage, disabled: it === '...' }]">
                  <a v-if="it !== '...'" href="javascript:;" class="page-link" @click="handlePageChange(it)">{{ it }}</a>
                  <a v-else href="javascript:;" class="page-link">…</a>
                </li>
                <li :class="['page-item next', { disabled: currentPage === totalPages }]">
                  <a href="javascript:;" class="page-link" @click="handlePageChange(Math.min(totalPages, currentPage + 1))">
                    <i class="ki-outline ki-arrow-right fs-3"></i>
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
  <div class="modal fade" id="kt_modal_crud" ref="modalEl" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content" :key="modalKey">
        <div class="modal-header">
          <h3 class="modal-title">{{ isEdit ? 'Edit Recursions (Group)' : 'Add Recursions' }}</h3>
          <button class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal">
            <i class="ki-outline ki-cross fs-1"></i>
          </button>
        </div>

        <div class="modal-body">
          <!-- Top-level fields -->
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label">Name</label>
              <input v-model="form.name" type="text" class="form-control" />
            </div>
            <div class="col-md-3">
              <label class="form-label">Recursion</label>
              <select v-model.number="form.recursion" class="form-select">
                <option :value="1">Monthly</option>
                <option :value="2">Weekly</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Times</label>
              <input v-model.number="form.recursion_number" type="number" min="0" class="form-control" />
              <small class="text-muted">0 = disabled</small>
            </div>
            <div class="col-md-2">
              <label class="form-label">Start Date</label>
              <input v-model="form.start_date" type="date" class="form-control" />
            </div>
            <div class="col-12">
              <label class="form-label">Note</label>
              <textarea v-model="form.note" class="form-control" rows="2" placeholder="Optional"></textarea>
            </div>
          </div>

          <!-- Totals + Add line -->
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
              <span class="badge" :class="isBalanced ? 'badge-success' : 'badge-danger'">
                Debit: {{ totalDebit.toFixed(2) }} | Credit: {{ totalCredit.toFixed(2) }}
                <span v-if="!isBalanced"> — Not balanced</span>
              </span>
            </div>
            <button class="btn btn-sm btn-light-primary" @click="addLine">
              <i class="ki-outline ki-plus fs-3 me-1"></i> Add line
            </button>
          </div>

          <!-- Dynamic lines (id is kept invisibly) -->
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th style="min-width:240px">Ledger</th>
                  <th style="width:140px">Type</th>
                  <th style="width:160px">Amount</th>
                  <th>Note</th>
                  <th style="width:60px"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(ln, idx) in lines" :key="ln._k">
                  <td>
                    <Select2Ledger v-model="ln.ledger" :dropdownParent="'#kt_modal_crud'" size="sm" />
                    <div v-if="ln.id" class="form-text">ID: #{{ ln.id }}</div>
                  </td>
                  <td>
                    <select v-model="ln.post_type" class="form-select form-select-sm">
                      <option value="debit">debit</option>
                      <option value="credit">credit</option>
                    </select>
                  </td>
                  <td>
                    <input v-model.number="ln.amount" type="number" step="0.01" min="0" class="form-control form-control-sm" />
                  </td>
                  <td>
                    <input v-model="ln.note" class="form-control form-control-sm" />
                  </td>
                  <td class="text-end">
                    <button class="btn btn-sm btn-light-danger" @click="removeLine(idx)" :disabled="lines.length <= 1" title="Remove">
                      <i class="ki-outline ki-trash fs-3"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

        </div>

        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
          <button class="btn btn-primary" :disabled="modalLoading || !isBalanced" @click="handleSubmit">
            <span v-if="modalLoading" class="spinner-border spinner-border-sm me-2"></span>
            {{ isEdit ? 'Update' : 'Save' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Optional tweaks */
</style>
