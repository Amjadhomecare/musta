<script setup>
import { ref, computed } from 'vue'
import { useMutation }        from '@tanstack/vue-query'
import { usePaginationQuery } from '@/composables/usePagination'
import dayjs  from 'dayjs'
import bankOptions from '../dd/bankOptions'




const TASK_OPTIONS = [
  { value: 1, label: 'Refund with cancellation' },
  { value: 2, label: 'Cancellation only' },
  { value: 3, label: 'Refund only' },
]

const cancelForm  = ref({ dd_id: null, note: '', task: 2 })
const cancelRow   = ref(null)
let   cancelModal = null

const { isPending: isCancelPending, mutate: sendCancel } = useMutation({
  mutationFn: payload => axios.post('/request-cancellation', payload),
  onSuccess : () => {
    cancelModal?.hide()
    Swal.fire('Success!', 'Cancellation request has been created.', 'success')
    refetch()
  },
  onError: (err) => {
    const msg = err?.response?.data?.message || err.message || 'Failed to create cancellation request'
    Swal.fire('Error!', msg, 'error')
  }
})

function openCancelModal(row) {
  // row.id is the direct_debits.id → becomes dd_id for the cancellation request
  cancelRow.value   = row
  cancelForm.value  = { dd_id: row.id, note: '' }
  cancelModal       = bootstrap.Modal.getOrCreateInstance('#cancelModal')
  cancelModal.show()
}

function submitCancellation() {
  if (!cancelForm.value.dd_id) {
    return Swal.fire('Error!', 'Missing Direct Debit ID.', 'error')
  }
  sendCancel({ ...cancelForm.value })
}



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


/* ────────────────────────────────────────────────────────────────
  1.  Form state
──────────────────────────────────────────────────────────────── */
const ddForm = ref(null)

function defaultForm () {
  return {
    payment_frequency: 'M',
    commences_on     : dayjs().add(1, 'month').startOf('month').format('YYYY-MM-DD'),
    expires_on : '' , 
    iban             : '',
    account_title    : '',
    account_type     : 'C',
    paying_bank_id   : '',
    paying_bank_name : '',
    customer_id      : null,
    customer_type    : 'IN',
    customer_id_no   : '',
    customer_id_type : 'EIDAC',
    fixed_amount     : null,
    phone            : '',
    note             : '',
    sign_url         : '', 
    sign_url_2       : '',
    resign_display   : false,
    send_sms         : false,
  }
}
const form      = ref(defaultForm())
const editingId = ref(null)
const refLookupCode = ref('')
const refLookupLoading = ref(false)

/* 1️⃣  declare it near the top */


/* ────────────────────────────────────────────────────────────────
  2.  Keen Select2  – remote customer search
──────────────────────────────────────────────────────────────── */
function initCustomerSelect (preId = null, preName = '') {
  const $sel = $('#customer_id_select')

  if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy')

  $sel.select2({
    dropdownParent : $('#ddModal'),
    placeholder    : 'Select customer',
    allowClear     : true,
    width          : '100%',
    ajax: {
      url      : '/all-customers',
      delay    : 250,
      dataType : 'json',
      data: params => ({
        search  : params.term,
        context : 'direct-debit',
        page    : params.page || 1
      }),
      processResults: ({ items }) => ({
        results: items.map(i => ({
          id     : i.id,     // required by Select2
          text   : i.text,   // label
          erp_id : i.erp_id  // numeric ERP ID
        }))
      }),
      cache: true
    },
    templateResult    : r => r.loading ? r.text : r.text,
    templateSelection : r => r.text || r.id,
    escapeMarkup      : m => m
  })
  .on('select2:select', e => { form.value.customer_id = e.params.data.erp_id })
  .on('select2:clear',  () => { form.value.customer_id = null })

  if (preId && preName) {
    const opt = new Option(preName, preName, true, true)
    $(opt).data('data', { id: preName, text: preName, erp_id: preId })
    $sel.append(opt).trigger('change')
  }
}

/* ────────────────────────────────────────────────────────────────
  3.  Modal helpers
──────────────────────────────────────────────────────────────── */
function openModal (cid = null, cname = '') {
  bootstrap.Modal.getOrCreateInstance('#ddModal').show()
  setTimeout(() => initCustomerSelect(cid, cname), 200)
}
function closeModal () {
  bootstrap.Modal.getInstance('#ddModal')?.hide()
  $('#customer_id_select').val(null).trigger('change')
  form.value.customer_id = null
  resetForm()
}
function resetForm () {
  form.value  = defaultForm()
  editingId.value = null
}

/* ────────────────────────────────────────────────────────────────
  4.  Table filters & pagination
──────────────────────────────────────────────────────────────── */
const filterVisible = ref(false)
const filters = { start_date: ref(''), end_date: ref(''), status: ref(null)  , active: ref(null) }

const startDateFilter = computed({
  get: () => filters.start_date.value,
  set: v  => { filters.start_date.value = v }
})
const endDateFilter = computed({
  get: () => filters.end_date.value,
  set: v  => { filters.end_date.value = v }
})
const statusFilter = computed({
  get: () => filters.status.value,
  set: v  => { filters.status.value = v }
})

const activeFilter = computed({
  get: () => filters.active.value,
  set: v  => { filters.active.value = v }
})

const {
  data,
  refetch,
  currentPage,
  pageSize,
  searchQuery,
  total,
  handlePageChange,
  handleSizeChange,
  exportToExcel,
  isLoading
} = usePaginationQuery({
  apiUrl: '/direct-debit-list',
  queryKeyPrefix: 'dd-list',
  filters
})
const totalPages = computed(() => Math.ceil(total.value / pageSize.value))

/* ────────────────────────────────────────────────────────────────
  5.  Edit existing row
──────────────────────────────────────────────────────────────── */
function editForm (row) {
  form.value = {
    payment_frequency : row.payment_frequency ?? 'M',
    commences_on      : row.commences_on ? dayjs(row.commences_on).format('YYYY-MM-DD') : '',
    expires_on :  row.expires_on ? dayjs(row.expires_on).format('YYYY-MM-DD') : '',
    iban              : row.iban ?? '',
    account_title     : row.account_title ?? '',
    account_type      : row.account_type ?? 'C',
    paying_bank_id    : row.paying_bank_id ?? '',
    paying_bank_name  : row.paying_bank_name ?? '',
    customer_id       : row.customer?.id ?? null,
    customer_type     : row.customer_type ?? 'IN',
    customer_id_no    : row.customer_id_no ?? '',
    customer_id_type  : row.customer_id_type ?? 'EIDAC',
    fixed_amount      : row.fixed_amount ?? null,
    phone             : row.phone ?? '',
    note              : row.note ?? '',
    resign            : row.status == 3 ? true : false, 
    resign_display    :    false,
    sign_url          : '',
    sign_url_2        : '',
  }

  refLookupCode.value = ''
  editingId.value = row.id
  openModal(form.value.customer_id, row.customer?.name)
}

/* Lookup direct debit by ref code and auto-fill form fields */
async function lookupByRef() {
  if (!refLookupCode.value.trim()) {
    return Swal.fire('Error!', 'Please enter a reference code.', 'error')
  }
  
  refLookupLoading.value = true
  try {
    const { data: res } = await axios.get(`/direct-debit/by-ref/${refLookupCode.value.trim()}`)
    
    if (res.success && res.data) {
      const dd = res.data
      // Auto-fill all fields EXCEPT: expires_on, note, and fixed_amount
      form.value.payment_frequency = dd.payment_frequency ?? form.value.payment_frequency
      form.value.iban = dd.iban ?? form.value.iban
      form.value.account_title = dd.account_title ?? form.value.account_title
      form.value.account_type = dd.account_type ?? form.value.account_type
      form.value.paying_bank_id = dd.paying_bank_id ?? form.value.paying_bank_id
      form.value.paying_bank_name = dd.paying_bank_name ?? form.value.paying_bank_name
      form.value.customer_type = dd.customer_type ?? form.value.customer_type
      form.value.customer_id_no = dd.customer_id_no ?? form.value.customer_id_no
      form.value.customer_id_type = dd.customer_id_type ?? form.value.customer_id_type
      form.value.phone = dd.phone ?? form.value.phone
      form.value.sign_url = dd.extra?.sign ?? ''
      form.value.sign_url_2 = dd.extra?.sign2 ?? ''
      
      // Update customer select if customer exists
      if (dd.customer?.id && dd.customer?.name) {
        form.value.customer_id = dd.customer.id
        initCustomerSelect(dd.customer.id, dd.customer.name)
      }
      
      Swal.fire('Success!', 'Form fields have been auto-filled from the reference.', 'success')
    } else {
      Swal.fire('Not Found!', res.message || 'No direct debit found with this reference.', 'warning')
    }
  } catch (error) {
    const msg = error?.response?.data?.message || error.message || 'Failed to lookup reference.'
    Swal.fire('Error!', msg, 'error')
  } finally {
    refLookupLoading.value = false
  }
}

/* ────────────────────────────────────────────────────────────────
  6.  Save / update
──────────────────────────────────────────────────────────────── */
const { isPending, mutate } = useMutation({
  mutationFn: async payload => {
    if (!payload.customer_id) throw new Error('Customer is required')
    if (payload.commences_on)
      payload.commences_on = dayjs(payload.commences_on).format('YYYY-MM-DD')
    if (editingId.value) payload.id = editingId.value
    return axios.post('/store-direct-debit', payload)
  },
  onSuccess: () => {
    const didSendSms = form.value.send_sms && form.value.phone
    closeModal()
    refetch()
    Swal.fire("Success!", didSendSms ? "Mandate created and SMS sent." : "Mandate has been created.", "success")
  },
  onError  : err => alert(err?.message || err.response?.data?.message || 'Failed to submit')
})
function submitForm () {

  if (!ddForm.value.reportValidity()) return  

  if (!form.value.customer_id)
    return Swal.fire("Error!", "An error oqured.", "error")

  mutate({ ...form.value, customer_id: Number(form.value.customer_id) })
}

/* ------------------------------------------------------------------
   File upload modal logic
------------------------------------------------------------------ */
/* reactive state ------------------------------------------------ */
const fileChosen  = ref(null)   // holds the File object
const fileLoading = ref(false)  // spinner flag while uploading
const currentRow  = ref({})     // row being edited
let   fileModal   = null        // Bootstrap modal instance

/* choose file --------------------------------------------------- */
function onFileChange (e) {
  fileChosen.value = e.target.files[0] || null
}

/* open modal ---------------------------------------------------- */
function openFileModal(row) {
  currentRow.value = JSON.parse(JSON.stringify(row)); // clone row
  fileChosen.value = null;
  fileModal = bootstrap.Modal.getOrCreateInstance('#fileModal');
  fileModal.show();
}

async function submitFile() {
  if (!fileChosen.value) return alert('Please choose a file.');
  fileLoading.value = true;
  try {
    const fd = new FormData();
    fd.append('ref', currentRow.value.ref);
    fd.append('file', fileChosen.value);

    const { data } = await axios.post('/direct-debits/upload-file', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
      validateStatus: s => s >= 200 && s < 300
    });

    alert(data.message || 'File uploaded successfully!');

    // Safe patch
    if (!currentRow.value.extra || typeof currentRow.value.extra !== 'object') {
      currentRow.value.extra = {};
    }
    currentRow.value.extra.file = data.file_url;

    fileModal.hide();
    refetch(); // refresh table data
  } catch (e) {
    alert(e?.response?.data?.message || e.message || 'Upload failed');
  } finally {
    fileLoading.value = false;
    fileChosen.value  = null;
  }
}


const pdfLoadingId = ref(null);

const generatePDF = async (id, name) => {
  pdfLoadingId.value = id;
  try {
    const response = await fetch(`https://homecaremaids.ae/api/dda?id=${id}&name=${name}`);
    if (!response.ok) throw new Error('Failed to generate PDF');
    const blob = await response.blob();
    const url = URL.createObjectURL(blob);
    window.open(url, '_blank');
    // Optional: download as well
    const a = document.createElement('a');
    a.href = url;
    a.download = `${name}.pdf`;
    a.click();
    URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error generating PDF:', error);
  } finally {
    pdfLoadingId.value = null;
  }
};


const pdfLoadingIdNoSign = ref(null);

const generatePDFNoSign = async (id, name) => {
  pdfLoadingIdNoSign.value = id;
  try {
    const response = await fetch(`https://homecaremaids.ae/api/dda?id=${id}&name=${name}&sign=false`);
    if (!response.ok) throw new Error('Failed to generate PDF');
    const blob = await response.blob();
    const url = URL.createObjectURL(blob);
    window.open(url, '_blank');
    // Optional: download as well
    const a = document.createElement('a');
    a.href = url;
    a.download = `${name}.pdf`;
    a.click();
    URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error generating PDF:', error);
  } finally {
    pdfLoadingIdNoSign.value = null;
  }
};


const deleteDirectDebit = async (id) => {
  if (!confirm('Are you sure you want to delete this Direct Debit?')) return;

  try {
    await axios.delete(`/direct-debit/${id}`);
    refetch();
    Swal.fire("Success!", "Direct Debit has been deleted.", "success");
  } catch (error) {
    Swal.fire("Error!", error?.response?.data?.message || 'Failed to delete Direct Debit.', "error");
  }
};

const smsLoadingId = ref(null);

const sendSmsLink = async (row) => {
  if (!row.phone) {
    return Swal.fire('Error!', 'No phone number available for this Direct Debit.', 'error');
  }
  
  smsLoadingId.value = row.id;
  try {
    const { data } = await axios.post('/direct-debit/send-sms', {
      ref: row.ref,
      phone: row.phone
    });
    
    if (data.success) {
      Swal.fire('Sent!', 'Signing link SMS sent successfully.', 'success');
    } else {
      Swal.fire('Error!', data.message || 'Failed to send SMS.', 'error');
    }
  } catch (error) {
    Swal.fire('Error!', error?.response?.data?.message || 'Failed to send SMS.', 'error');
  } finally {
    smsLoadingId.value = null;
  }
};
/* ────────────────────────────────────────────────────────────────
  8.  Static data & helpers
──────────────────────────────────────────────────────────────── */
bankOptions

function handleBankChange (code) {
  const bank = bankOptions.find(b => b.value === code)
  form.value.paying_bank_name = bank ? bank.label : ''
}

const customerIdTypeOptions = [
  { value: 'EIDAC', label: 'Emirates ID' },

]



const formatFrequency   = c => c === 'M' ? 'Monthly'
  : c === 'Q' ? 'Quarterly' : c === 'A' ? 'Annual' : c


const formatStatus      = s => s == 0 ? 'Created'
  : s == 1 ? 'Accepted' : s == 2 ? 'Pending' : s == 3 ? 'Rejected' : s== 4 ? 'Resign Requested' : 'Unknown'

const getStatusClass    = s => s == 0 ? 'badge badge-light-warning'
  : s == 1 ? 'badge badge-success'
  : s == 2 ? 'badge badge-info'
  : s == 3 ? 'badge badge-danger'
  : s == 4 ? 'badge badge-info' : 'badge badge-light-secondary'



const truncate          = (s, l = 15) => (!s ? '—' : s.length > l ? `${s.slice(0, l)}…` : s)
const formatDateTime    = d => d ? dayjs(d).format('DD-MMM-YY HH:mm') : '—'

const formatMoney       = v => v == null ? '—'
  : new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(v)


const formatDate        = d => d ? dayjs(d).format('DD-MMM-YYYY') : '—'

const formatAccountType = c => c === 'C' ? 'Current / Saving'
  : c === 'O' ? 'Credit Card' : c || '—'



</script>


<template>

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
  <!--begin::Content container-->
  <div id="kt_app_content_container" class="app-container container-fluid">

    <!--begin::Card-->
    <div class="card card-flush shadow-sm mb-10 position-relative">

   <!--begin::Card header-->
      <div class="card-header border-0 py-5">

  <!-- بحث -->
  <div class="d-flex align-items-center position-relative my-1 me-auto">
    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6">
      <span class="path1"></span><span class="path2"></span>
    </i>
    <input v-model="searchQuery"
           type="text"
           class="form-control form-control-solid w-250px ps-15"
           placeholder="Search Customers" />
  </div>

  <!-- Controls -->
  <div class="card-toolbar d-flex gap-2"><!--  ❱❱ gap-2 = 0.5rem ؛ غيِّر إلى gap-3 أو gap-4 إذا أردت مسافة أكبر  -->
  
    <button class="btn btn-light-primary" @click="filterVisible = !filterVisible">
          <i class="ki-duotone ki-filter-tablet fs-2 me-1">
      <span class="path1"></span><span class="path2"></span>
    </i>
       Filter
       
    </button>

    <button class="btn btn-light-primary" @click="exportToExcel">
       <i class="ki-duotone ki-exit-up fs-2">
          <span class="path1"></span>
          <span class="path2"></span>

    </i> Excel
    </button>

    <button class="btn btn-primary" @click="openModal">
      <i class="ki-duotone ki-plus fs-2 me-1"></i> New 
    </button>
  </div>

  
</div>
<!--end::Card header-->


      <!--begin::Filters-->
      <div class="card-body pt-0">
        <transition name="collapse">
          <div v-if="filterVisible" class="border rounded p-4 mb-5">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label fw-semibold">Start Date</label>
                <input v-model="startDateFilter" type="date" class="form-control" />
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">End Date</label>
                <input v-model="endDateFilter" type="date" class="form-control" />
              </div>
              <div class="col-md-4">
                <label   class="form-label fw-semibold ">Status</label>
                <select v-model="statusFilter" class="form-select">
                  <option value="">All</option>
                  <option value="0">Created</option>
                  <option value="1">Accepted</option>
                  <option value="2">Pending</option>
                  <option value="3">Rejected</option>
                  <option value="4">Resign Requested</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label fw-semibold">Active</label>
                <select v-model="activeFilter" class="form-select">
                  <option value="">All</option>
                  <option value="0">Active</option>
                  <option value="1">Inactive</option>
                  <option value="2"> No contract in hc </option>
                  <option value="3"> Active in hc but no active contract </option>
                </select>
              </div>
            </div>
          </div>
        </transition>

        <!-- Total count -->
            <div class="mb-3">
              <span class="badge bg-light fw-semibold">
                Total Records: {{ total }}
              </span>
            </div>

          <!--begin::Table-->
          <div class="table-responsive">
            
            <table class="table table-row-bordered table-row-gray-300 align-middle gy-4">
              <thead class="fw-bold text-gray-700 text-uppercase">
                <tr>
                  <th>Ref</th>
                  <th>Freq</th>
                  <th>Account&nbsp;Title</th>
                  <th>Amount</th>
                  <th>Active</th>
                  <th>Cust.</th>
                  <th>Attachment</th>
                  <th>Note</th>
                  <th>Reject Reason</th>
                  <th>Center&nbsp;Ref</th>
                  <th>Status</th>
                  <th>Sign</th>
                  <th>Created</th>
                  <th>Updated </th>
                  <th>User </th>
                  <th>Update by</th>
                  
                  <th class="text-end min-w-125px">Actions</th>
                </tr>
              </thead>

    <tbody class="fw-semibold text-gray-900">

        <!-- loading row – shows only while data is being fetched -->
      <tr v-if="isLoading">
        <!-- 25 columns? adjust to match your <th> count -->
        <td :colspan="25" class="text-center py-8">
          <span class="spinner-border text-primary me-2" role="status"></span>
       
        </td>
      </tr>
      <tr v-for="row in data?.data || []" :key="row.id"> 
          <!-- الأساسيات -->
        <td>
          <a :href="`https://sign.homecaremaids.ae/sign-dd/${row.ref}`" target="_blank">
            {{ row.ref }}
          </a>
        </td>

       <!-- Payment frequency column -->
      <td>
        <span class="text-gray-800 fw-semibold cursor-pointer"
              @click="generatePDF(row.id, row.account_title)">
          <!-- tiny spinner while waiting -->
          <span v-if="pdfLoadingId === row.id"
                class="spinner-border spinner-border-sm me-1"></span>

          {{ formatFrequency(row.payment_frequency) }}
        </span>
      </td>

<td>
  <span class="text-gray-800 fw-semibold user-select-all cursor-pointer"
        @click="generatePDFNoSign(row.id, row.account_title)">
    <span v-if="pdfLoadingIdNoSign === row.id"
          class="spinner-border spinner-border-sm me-1"></span>

    {{ row.account_title }}
  </span>
</td>

        <td class="text-end">{{ formatMoney(row.fixed_amount) }}</td>

        <!-- نشط؟ -->
        <td>
          <span :class="row.active ? 'badge badge-light-danger' : 'badge badge-light-success'">
            {{ row.active ? 'canceled' : '-' }}
          </span>
        </td>

        <!-- العميل -->
        <td>
          <a :href="`/customer/report/p4/${row.customer?.name}`" class="text-primary fw-bold">
            {{ row.customer?.name || '—' }}
          </a>
        </td>


        <!-- PDF -->
<!-- Attachment column -->
<td>
      <!-- if a file already exists → show both buttons side‑by‑side -->
      <template v-if="row.extra?.file">
        <div class="btn-group btn-group-sm" role="group">
          <!-- view / download -->
          <a  :href="row.extra.file"
              target="_blank"
              class="btn btn-light">
            Open
          </a>

          <!-- replace file -->
          <button class="btn btn-light"
                  @click="openFileModal(row)">
            Edit
          </button>
        </div>
      </template>

      <!-- no file yet → single Upload button -->
      <template v-else>
        <button class="btn btn-sm btn-light-primary"
                @click="openFileModal(row)">
          Upload
        </button>
      </template>
    </td>




        <!-- الملاحظات -->
        <td>
          <span v-if="row.note"
                :title="row.note">{{ truncate(row.note, 30) }}</span>
        </td>
        <td>
          <span v-if="row.rejected_reason"
                :title="row.rejected_reason">{{ truncate(row.rejected_reason, 30) }}</span>
        </td>

        <td>{{ row.center_bank_ref || '—' }}</td>

        <!-- الحالة -->
        <td>
          <span :class="['badge px-4', getStatusClass(row.status)]">
            {{ formatStatus(row.status) }}
          </span>
        </td>

        <!-- التوقيع -->
        <td>
          <img v-if="row.extra?.sign"
               :src="row.extra.sign"
               class="h-40px w-auto rounded shadow-sm"
               alt="signature" />
        </td>

        <!-- أنشئ في -->
        <td>{{ formatDateTime(row.created_at) }}</td>
        <td>{{ formatDateTime(row.updated_at) }}</td>

        <td>{{ row.created_by }} </td>
        <td>{{ row.updated_by }}</td>


        <!-- أكشن -->
<td class="text-end">
  <div class="btn-group" role="group">
    <button class="btn btn-sm btn-light btn-active-light-primary"
            @click="editForm(row)">
      Edit
    </button>
    <button class="btn btn-sm btn-light btn-active-light-success"
            :disabled="smsLoadingId === row.id"
            @click="sendSmsLink(row)">
      <span v-if="smsLoadingId === row.id" class="spinner-border spinner-border-sm me-1"></span>
      SMS
    </button>

    <button class="btn btn-sm btn-light btn-active-light-danger"
            @click="deleteDirectDebit(row.id)">
      Delete
    </button>


  </div>
</td>


      </tr>
    </tbody>
  </table>
</div>
<!--end::Table-->

        <!--begin::Pagination-->
        <div class="d-flex justify-content-between align-items-center mt-5 flex-wrap gap-3">
          <div>
            <label class="me-2 fw-semibold">Page size:</label>
            <select v-model="pageSize"
                    @change="handleSizeChange($event.target.value)"
                    class="form-select form-select-sm w-auto d-inline-block">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
              <option value="200">200</option>
              <option value="500">500</option>
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
        <!--end::Pagination-->
      </div>
      <!--end::Filters / body-->
    </div>
    <!--end::Card-->

  </div>
  <!--end::Content container-->
</div>
<!--end::Content wrapper-->


<!-- Modal section -->

<!-- Direct‑Debit Modal -->
<div class="modal fade" id="ddModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- header -->
      <div class="modal-header">
        <h5 class="modal-title">
          {{ editingId ? 'Edit Direct Debit' : 'Add Direct Debit' }}
        </h5>
        <button type="button" class="btn-close" @click="closeModal"></button>
      </div>

      <!-- body -->
      <div class="modal-body">

        <!-- Ref Code Lookup (only when editing) - styled at top -->
        <div v-if="editingId" class="card bg-light-primary border-0 mb-5">
          <div class="card-body py-4">
            <div class="d-flex align-items-center mb-2">
              <i class="ki-duotone ki-magnifier fs-2 text-primary me-2">
                <span class="path1"></span><span class="path2"></span>
              </i>
              <h6 class="mb-0 fw-bold text-primary">Lookup by Ref Code</h6>
            </div>
            <p class="text-muted small mb-3">Auto-fill all fields (except Expires On , Commences On, Note, and Fixed Amount) from an existing record.</p>
            <div class="input-group">
              <input v-model="refLookupCode"
                     type="text"
                     class="form-control form-control-solid"
                     placeholder="Enter ref code..."
                     @keyup.enter="lookupByRef" />
              <button class="btn btn-primary"
                      type="button"
                      :disabled="refLookupLoading"
                      @click="lookupByRef">
                <span v-if="refLookupLoading" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="ki-duotone ki-arrow-right fs-4"></i>
                Lookup
              </button>
            </div>
          </div>
        </div>

        <!-- real form so HTML5 validation fires -->
        <form   id="ddForm" 
                 ref="ddForm"
               @submit.prevent="submitForm"
               class="row g-3"
               novalidate>

          <!-- 1.  Payment frequency -->
          <div class="col-md-6">
            <label class="form-label">Payment Frequency</label>
            <select v-model="form.payment_frequency"
                    class="form-select"
                    required>
              <option value="M">Monthly</option>
              <option value="O">One time</option>
              <option value="Q">Quarterly</option>
              <option value="A">Annual</option>
            </select>
          </div>

          <!-- 2.  Commences / Expires -->
          <div class="col-md-6">
            <label class="form-label">Commences On</label>
            <input v-model="form.commences_on"
                   type="date"
                   class="form-control"
                   required />
          </div>

          <div class="col-md-6">
            <label class="form-label">
              Expires On <span class="text-muted">(optional)</span>
            </label>
            <input v-model="form.expires_on"
                   type="date"
                   class="form-control" />
          </div>

          <!-- 3.  IBAN / Account title (only shown when editing) -->
          <div v-if="editingId" class="col-md-6">
            <label class="form-label">IBAN</label>
            <input v-model="form.iban"
                   class="form-control"
                   pattern="^[A-Za-z0-9]+$" />
          </div>

          <div v-if="editingId" class="col-md-6">
            <label class="form-label">Account Title</label>
            <input v-model="form.account_title"
                   class="form-control" />
          </div>

          <!-- 4.  Account type / Paying bank (only shown when editing) -->
          <div v-if="editingId" class="col-md-6">
            <label class="form-label">Account Type</label>
            <select v-model="form.account_type"
                    class="form-select">
              <option value="C">Current/Saving</option>
              <option value="O">Credit Card</option>
            </select>
          </div>

          <div v-if="editingId" class="col-md-6">
            <label class="form-label">Paying Bank</label>
            <select v-model="form.paying_bank_id"
                    class="form-select"
                    @change="handleBankChange(form.paying_bank_id)">
              <option v-for="b in bankOptions"
                      :key="b.value"
                      :value="b.value">
                {{ b.label }}
              </option>
            </select>
          </div>

          <!-- 5.  Customer (Select2) -->
          <div class="col-md-6">
            <label class="form-label">Customer</label>
            <select  id="customer_id_select"
                     class="form-select"
                     data-control="select2"
                     data-hide-search="false"
                     data-placeholder="Select customer"
                     required>
            </select>
          </div>

          <!-- 6.  Payer type / ID number / ID type (only shown when editing) -->
          <div v-if="editingId" class="col-md-6">
            <label class="form-label">Payer Type</label>
            <select v-model="form.customer_type"
                    class="form-select">
              <option value="IN">Individual</option>
            </select>
          </div>

          <div v-if="editingId" class="col-md-6">
            <label class="form-label">Paying ID Number</label>
            <input v-model="form.customer_id_no"
                   class="form-control"
                   pattern="^[A-Za-z0-9]+$" />
          </div>

          <div v-if="editingId" class="col-md-6">
            <label class="form-label">Customer ID Type</label>
            <select v-model="form.customer_id_type"
                    class="form-select">
              <option v-for="opt in customerIdTypeOptions"
                      :key="opt.value"
                      :value="opt.value">
                {{ opt.label }}
              </option>
            </select>
          </div>

          <!-- 7.  Amount / Phone -->
          <div class="col-md-6">
            <label class="form-label">Fixed Amount</label>
            <input v-model="form.fixed_amount"
                   type="number"
                   step="0.01"
                   class="form-control"
                   required />
          </div>

          <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input v-model="form.phone"
                   class="form-control"
                   pattern="^5[0-9]{8}$"
                   required />
          </div>

          <!-- 8.  Note (optional) -->
          <div class="col-12">
            <label class="form-label">
              Note <span class="text-muted">(optional)</span>
            </label>
            <textarea v-model="form.note"
                      class="form-control"
                      rows="2"></textarea>
          </div>

          <!-- 9. Send SMS checkbox -->
          <div class="col-12">
            <div class="form-check">
              <input v-model="form.send_sms"
                     class="form-check-input"
                     type="checkbox"
                     id="sendSmsCheck" />
              <label class="form-check-label" for="sendSmsCheck">
                Send signing link via SMS after submit
              </label>
            </div>
          </div>

          <!-- 10.  Resign notify (only when editing and status is Rejected) -->
         <div v-if="form.resign" class="form-check form-switch mb-10">
                <input v-model="form.resign_display" class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                <label class="form-check-label" > Customer resign </label>
            </div>



          <!-- 10. Signature URL  -->
          <div v-if="editingId" class="col-md-6">
            <label class="form-label">
              Signature URL 1 <span class="text-muted">(optional)</span>
            </label>
            <input v-model="form.sign_url"
                   type="url"
                   class="form-control" />
          </div>

          <!-- 11. Signature URL 2 -->
          <div v-if="editingId" class="col-md-6">
            <label class="form-label">
              Signature URL 2 <span class="text-muted">(optional)</span>
            </label>
            <input v-model="form.sign_url_2"
                   type="url"
                   class="form-control" />
          </div>

        </form>
      </div>

      <!-- footer -->
      <div class="modal-footer">
        <button class="btn btn-secondary"
                type="button"
                @click="closeModal">
          Cancel
        </button>

        <button class="btn btn-primary"
                type="submit"
                form="ddForm"
                :disabled="isPending">
          Submit
        </button>
      </div>
    </div>
  </div>
</div>


  <!-- PDF Upload Modal (Bootstrap) -->
<!-- Generic File Upload Modal -->
<div class="modal fade" id="fileModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          Upload file for {{ currentRow?.ref }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Choose file (max 10 MB)</label>
          <input type="file"
                 class="form-control"
                 @change="onFileChange" />
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary"
                :disabled="fileLoading || !fileChosen"
                @click="submitFile">
          <span v-if="fileLoading"
                class="spinner-border spinner-border-sm me-1"></span>
          Upload
        </button>
      </div>
    </div>
  </div>
</div>


<!-- Cancellation Request Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- header -->
      <div class="modal-header">
        <h5 class="modal-title">
          Request Cancellation
          <small v-if="cancelRow?.ref" class="text-muted ms-2">Ref: {{ cancelRow.ref }}</small>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- body -->
      <div class="modal-body">

                <!-- NEW: Task select -->
          <div class="mb-3">
            <label class="form-label">Task</label>
            <select v-model.number="cancelForm.task" class="form-select" required>
              <option v-for="opt in TASK_OPTIONS" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </option>
            </select>
          </div>


        <div class="mb-3">
          <label class="form-label">Reason / Note <span class="text-muted">(optional)</span></label>
          <textarea v-model="cancelForm.note"
                    class="form-control"
                    rows="3"
                    placeholder="Why are you cancelling?"></textarea>
        </div>

        <div class="alert alert-info d-flex align-items-center" role="alert">
          <i class="ki-duotone ki-information-2 fs-2 me-2"></i>
          <div>
            This will create a new <strong>cancellation request</strong> for the selected mandate.
          </div>
        </div>
      </div>

      <!-- footer -->
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-warning"
                :disabled="isCancelPending"
                @click="submitCancellation">
          <span v-if="isCancelPending" class="spinner-border spinner-border-sm me-2"></span>
          Submit Request
        </button>
      </div>
    </div>
  </div>
</div>



</template>

<style scoped>

</style>