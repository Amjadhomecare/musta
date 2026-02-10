<script setup>
import { ref, computed } from 'vue'
import { useMutation }        from '@tanstack/vue-query'
import { usePaginationQuery } from '@/composables/usePagination'
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
  apiUrl: '/dd-followup/rejected',
  queryKeyPrefix: 'dd-list',
  filters
})
const totalPages = computed(() => Math.ceil(total.value / pageSize.value))








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



  const copyLink = (ref) => {
  if (!ref) return
  const link = `https://sign.homecaremaids.ae/external/update-dd/${ref}`
  
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
           
                  <th>Account&nbsp;Title</th>
                  <th>Amount</th>
                  <th>Active</th>
                  <th>Cust.</th>
                  <th>Note</th>
                  <th>Reject Reason</th>
                  <th>Center&nbsp;Ref</th>
                  <th>Status</th>
                  <th>Sign</th>
                  <th>Created</th>
                  <th>Updated </th>
                  <th>User </th>
                  <th>Update by</th>
                  
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
          <a :href="`/vue/direct-debit?search=${row.ref}`">
            {{ row.ref }}
          </a>
        </td>

        <td>
   
      
            {{ row.account_title }}
        
        </td>
             <td>
                    <div class="d-flex flex-column gap-1">
                      <span class="fw-semibold">AED {{ row.fixed_amount ?? '—' }}</span>
                      <button
                        v-if="row.ref"
                        @click="copyLink(row.ref)"
                        class="btn btn-sm btn-light-primary"
                        :title="`Copy link: https://sign.homecaremaids.ae/external/update-dd/${row.ref}`"
                      >
                        <i class="ki-duotone ki-copy fs-5">
                          <span class="path1"></span>
                          <span class="path2"></span>
                        </i>
                        Update bank details
                      </button>
                    </div>
                  </td>

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






</template>

<style scoped>

</style>