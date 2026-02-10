
<script setup>
import { ref, reactive, computed , watch } from 'vue'
import { useMutation } from '@tanstack/vue-query'
import { usePaginationQuery } from '@/composables/usePagination'

const filters = {
  start_date: ref(''),
  end_date: ref(''),
  reason: ref('')
}

const startDateFilter = computed({
  get: () => filters.start_date.value,
  set: val => (filters.start_date.value = val)
})
const endDateFilter = computed({
  get: () => filters.end_date.value,
  set: val => (filters.end_date.value = val)
})

const typeFilter = computed({
  get: () => filters.reason.value,
 
  set: val => (filters.reason.value = val)
})



const totalPages = computed(() => Math.ceil(total.value / pageSize.value))

// ✅ FORM MANAGEMENT
const isEditing = ref(false)
const editId = ref(null)
const modalLoading = ref(false)

const form = reactive({
  maid_name: '',
  last_entry_date: null,
  travel_date: null,
  dedcution: null,
  ticket: null,
  allowance: 0,
  note: '',
  reason: '',
  for: 'staff',
  remaining_amount: 0,
  pp: '',
  pp_expire: null,
  emirate_id: null,
  job_title: null,
  basic_salary: 0,
  salary_dh: 0
})

function resetForm() {
  Object.assign(form, {
    maid_name: '',
    last_entry_date: null,
    travel_date: null,
    dedcution: null,
    ticket: null,
    allowance: 0,
    note: '',
    reason: '',
    for: 'staff',
    remaining_amount: 0,
    pp: '',
    pp_expire: null,
    emirate_id: null,
    job_title: null,
    basic_salary: 0,
    salary_dh: 0
  })
}

function openModal() {
  isEditing.value = false
  editId.value = null
  resetForm()
  bootstrap.Modal.getOrCreateInstance('#kt_modal_1').show()
}

function showEditModal(item) {
  isEditing.value = true
  editId.value = item.id
  Object.assign(form, {
    ...item,
    last_entry_date: item.last_entry_date ? new Date(item.last_entry_date) : null,
    travel_date: item.travel_date ? new Date(item.travel_date) : null,
    pp_expire: item.pp_expire ? new Date(item.pp_expire) : null
  })
  bootstrap.Modal.getOrCreateInstance('#kt_modal_1').show()
}

// ✅ MUTATION WITH SWEETALERT NOTIFICATIONS
const { mutate } = useMutation({
  mutationFn: async (formData) => {
    const payload = {
      ...formData,
      last_entry_date: formData.last_entry_date
        ? new Date(formData.last_entry_date).toISOString().split('T')[0]
        : null,
      travel_date: formData.travel_date
        ? new Date(formData.travel_date).toISOString().split('T')[0]
        : null,
      pp_expire: formData.pp_expire
        ? new Date(formData.pp_expire).toISOString().split('T')[0]
        : null
    }

    if (editId.value) {
      return axios.put(`/staff-leave-salaries/${editId.value}`, payload)
    } else {
      return axios.post('/store-staff-leave-salary', payload)
    }
  },
  onMutate: () => {
    modalLoading.value = true
  },
  onSuccess: (res) => {
    Swal.fire('Success!', res.data.message || 'Staff Leave salary has been saved.', 'success')
    refetch() 
    bootstrap.Modal.getOrCreateInstance('#kt_modal_1').hide()
    resetForm()
    isEditing.value = false
    editId.value = null
  },
  onError: (err) => {
    Swal.fire('Error', err.response?.data?.message || err.message, 'error')
  },
  onSettled: () => {
    modalLoading.value = false
  }
})

// ✅ HANDLE SUBMIT BUTTON
function handleSubmit() {
  mutate({ ...form })
}


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
  apiUrl: '/leave-salaries-staff',
  queryKeyPrefix: 'amir',
  filters
})


// Selected IDs
const selectedIds = ref([])
// ✅ helpers to read the rows once
const rows = computed(() => data.value?.data ?? [])

// ✅ computed – are all rows selected?
const allSelected = computed(() =>
  rows.value.length > 0 &&
  rows.value.every(item => selectedIds.value.includes(String(item.id)))
)

// ✅ toggle everything
function toggleSelectAll () {
  if (allSelected.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = rows.value.map(item => String(item.id))
  }
}

// ✅ clear the selection whenever a new page/data set arrives
watch(rows, () => { selectedIds.value = [] })


</script>


<template>

  <div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container responsive" class="app-container container-xxl">

      <h1 class="d-flex align-items-center text-grey-900 fw-bolder fs-3 my-1 pb-4">Staff Clearence</h1>

      <div class="card">
        <!-- Card header -->
        <div class="card-header border-0 pt-6">
          <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
              <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                <span class="path1"></span>
                <span class="path2"></span>
              </i>
              <input type="text" v-model="searchQuery" class="form-control form-control-solid w-250px ps-12" placeholder="Type to Search ..." />
            </div>
          </div>

          <div class="card-toolbar">
            <div class="d-flex justify-content-end">
              <!-- Filter button -->
              <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                <i class="ki-duotone ki-filter fs-2">
                  <span class="path1"></span>
                  <span class="path2"></span>
                </i>Filter
              </button>

              <!-- Filter dropdown -->
              <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true" id="kt-toolbar-filter">
                <div class="px-7 py-5">
                  <div class="fs-4 text-gray-900 fw-bold">Filter Options</div>
                </div>
                <div class="separator border-gray-200"></div>
                <div class="px-7 py-5">
                  <div class="mb-10">
                    <label class="form-label fs-5 fw-semibold mb-3">Type:</label>
                    <select v-model="typeFilter" 
                            class="form-select form-select-solid fw-bold" 
                            
                           >
                      <option></option>
                      <option value="cancel">Cancel</option>
                      <option value="renewal">Renewal</option>
                    </select>
                  </div>
                  <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary" data-kt-menu-dismiss="true" @click="refetch">Apply</button>
                  </div>
                </div>
              </div>

              <!-- Export & Add buttons -->
              <button type="button" class="btn btn-light-primary me-3" @click="exportToExcel">
                <i class="ki-duotone ki-exit-up fs-2"><span class="path1"></span><span class="path2"></span></i>Export
              </button>
              <button class="btn btn-primary" @click="openModal">+ Add</button>
            </div>
          </div>
        </div>

       <div class="ps-8 pt-4">
         <span class="badge badge-light-secondary">Total: {{ total }}</span>
       </div>

        <!-- Card body: table -->
        <div class="card-body pt-0">
            <div class="table-responsive">
          <table class="table align-middle table-row-dashed fs-6 gy-5 ">
              <thead>
                  <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                     <th class="w-10px pe-2">
                      <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                     <input
                          class="form-check-input"
                          type="checkbox"
                          :checked="allSelected"
                          @change="toggleSelectAll"
                        />

                          </div>
                          </th>
                            <th>Name</th>
                            <th>Last Entry Date</th>
                            <th>Travel Date</th>
                            <th>Deduction</th>
                            <th>Ticket</th>
                            <th>Allowance</th>
                            <th>Note</th>
                            <th>Reason</th>
                            <th>For</th>
                            <th>Remaining Amount</th>
                            <th>PP</th>
                            <th>PP Expire</th>
                            <th>Emirate ID</th>
                            <th>Job Title</th>
                            <th>Basic Salary</th>
                            <th>Salary DH</th>
                            <th class="text-end">Actions</th>
                  </tr>
                </thead>

                <tbody class="fw-semibold text-gray-600">
                  <tr v-if="isLoading">
                    <td :colspan="18" class="text-center py-10">
                      <div class="spinner-border text-primary me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                      Loading data, please wait...
                    </td>
                  </tr>

                  <tr v-for="item in data?.data" :key="item.id">
                  <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                <input
                        class="form-check-input"
                        type="checkbox"
                        :value="String(item.id)"  
                        v-model="selectedIds"
                      />

                 </div>
                  </td>

                    <td><a :href="`/staff-clearence/${item.id}`">{{ item.maid_name || 'N/A' }}</a></td>
                    <td>{{ item.last_entry_date || 'N/A' }}</td>
                    <td>{{ item.travel_date || 'N/A' }}</td>
                    <td>{{ item.dedcution || 0 }}</td>
                    <td>{{ item.ticket || 0 }}</td>
                    <td>{{ item.allowance || 0 }}</td>
                    <td>{{ item.note || 'N/A' }}</td>
                    <td>{{ item.reason || 'N/A' }}</td>
                    <td>{{ item.for || 'staff' }}</td>
                    <td>{{ item.remaining_amount || 0 }}</td>
                    <td>{{ item.pp || 'N/A' }}</td>
                    <td>{{ item.pp_expire || 'N/A' }}</td>
                    <td>{{ item.emirate_id || 'N/A' }}</td>
                    <td>{{ item.job_title || 'N/A' }}</td>
                    <td>{{ item.basic_salary || 0 }}</td>
                    <td>{{ item.salary_dh || 0 }}</td>
                    <td class="text-end">
                      <button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" @click="showEditModal(item)">
                        <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
          </table>

          <!-- Pagination + Per page controls -->
          <div class="d-flex justify-content-between align-items-center mt-5 flex-wrap gap-3">
            <div>
              <label class="me-2 fw-semibold">Page size:</label>
              <select v-model="pageSize"
                  @change="handleSizeChange(parseInt($event.target.value))"
                      class="form-select form-select-sm w-auto d-inline-block">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="500">500</option>
                    <option value="5000">5000</option>
              </select>
            </div>
           
            <ul class="pagination mb-0">
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <button class="page-link" @click="currentPage > 1 && handlePageChange(currentPage - 1)">
                  ‹
                </button>
              </li>
              <li v-for="page in Array.from({ length: totalPages }, (_, i) => i + 1)"
                  :key="page"
                  class="page-item"
                  :class="{ active: currentPage === page }">
                <button class="page-link" @click="handlePageChange(page)">
                  {{ page }}
                </button>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <button class="page-link" @click="currentPage < totalPages && handlePageChange(currentPage + 1)">
                  ›
                </button>
              </li>
          </ul>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
  
<!-- Modal -->
<div class="modal fade" id="kt_modal_1" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">{{ isEditing ? 'Edit Mandate' : 'Add Mandate' }}</h3>
        <button type="button" class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal">
          <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
        </button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Staff Name</label>
            <input v-model="form.maid_name" type="text" class="form-control" />
          </div>

          <div class="col-md-6">
            <label class="form-label">Last Entry Date</label>
            <input v-model="form.last_entry_date" type="date" class="form-control" />
          </div>

          <div class="col-md-6">
            <label class="form-label">Travel Date</label>
            <input v-model="form.travel_date" type="date" class="form-control" />
          </div>

          <div class="col-md-6">
            <label class="form-label">Deduction</label>
            <input v-model="form.dedcution" type="number" step="0.01" class="form-control" />
          </div>

          <div class="col-md-6">
            <label class="form-label">Ticket</label>
            <input v-model="form.ticket" type="number" step="0.01" class="form-control" />
          </div>

          <div class="col-md-6">
            <label class="form-label">Allowance</label>
            <input v-model="form.allowance" type="number" step="0.01" class="form-control" />
          </div>

          <div class="col-md-12">
            <label class="form-label">Note</label>
            <textarea v-model="form.note" class="form-control" rows="2"></textarea>
          </div>

          <div class="col-md-6">
            <label class="form-label">Reason</label>
            <select   v-model="form.reason" class="form-select">
              <option value="">Select Reason</option>
              <option value="renewal">Renewal</option>
              <option value="cancel">Cancel</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">For</label>
            <input v-model="form.for" type="text" class="form-control" readonly />
          </div>

          <div class="col-md-6">
            <label class="form-label">Remaining Amount</label>
            <input v-model="form.remaining_amount" type="number" step="0.01" class="form-control" />
          </div>

          <div class="col-md-6">
            <label class="form-label">Passport (PP)</label>
            <input v-model="form.pp" type="text" class="form-control" />
          </div>

          <div class="col-md-6">
            <label class="form-label">PP Expire Date</label>
            <input v-model="form.pp_expire" type="date" class="form-control" />
          </div>

          <div class="col-md-6">
            <label class="form-label">Emirate ID</label>
            <input v-model="form.emirate_id" type="text" class="form-control" />
          </div>

          <div class="col-md-6">
            <label class="form-label">Job Title</label>
            <input v-model="form.job_title" type="text" class="form-control" />
          </div>

          <div class="col-md-6">
            <label class="form-label">Basic Salary</label>
            <input v-model="form.basic_salary" type="number" step="0.01" class="form-control" />
          </div>

          <div class="col-md-6">
            <label class="form-label">Salary DH</label>
            <input v-model="form.salary_dh" type="number" step="0.01" class="form-control" />
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" :disabled="modalLoading" @click="handleSubmit">
          <span v-if="modalLoading" class="spinner-border spinner-border-sm"></span>
          {{ isEditing ? 'Update' : 'Save' }}
        </button>
      </div>
    </div>
  </div>
</div>

</template>

<style scoped>

</style>
