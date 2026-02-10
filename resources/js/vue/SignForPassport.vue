<script setup>
import { ref, reactive } from 'vue'
import { VueSignaturePad } from '@selemondev/vue3-signature-pad'
import { useMutation } from '@tanstack/vue-query'
import axios from 'axios'
import RemoteSelect from '../components/RemoteSelect.vue'

/* -----------------------------------------------
   🔄 Form state & validation
------------------------------------------------- */
const formRef = ref()

const selectedMaid = ref('')
const selectedCustomer = ref('')
const note = ref('')

// Toast notification state
const toast = ref({
  show: false,
  message: '',
  type: 'info' // 'success', 'warning', 'danger', 'info'
})

const showToast = (message, type = 'info') => {
  toast.value = { show: true, message, type }
  setTimeout(() => {
    toast.value.show = false
  }, 3000)
}

// Form errors
const errors = ref({
  selectedMaid: '',
  selectedCustomer: '',
  note: ''
})

const state = reactive({
  step: 1,
  customer: '',
  staff: ''
})

const customerPad = ref(null)
const staffPad = ref(null)

function grabPadImage(padRef) {
  if (!padRef?.value || padRef.value.isCanvasEmpty()) return ''
  return padRef.value.saveSignature()
}

const handleClearCustomer = () => customerPad.value?.clearCanvas()
const handleClearStaff = () => staffPad.value?.clearCanvas()

function nextStep() {
  const img = grabPadImage(customerPad)
  if (!img) {
    showToast('Please sign as customer first.', 'warning')
    return
  }
  state.customer = img
  state.step = 2
}

function clearPad(padRef) {
  padRef?.value?.clearCanvas()
}

function reset() {
  state.step = 1
  state.customer = ''
  state.staff = ''

  clearPad(customerPad)
  clearPad(staffPad)

  selectedMaid.value = ''
  selectedCustomer.value = ''
  note.value = ''
  errors.value = {
    selectedMaid: '',
    selectedCustomer: '',
    note: ''
  }
}

/* -----------------------------------------------
   🛠️  Mutation
------------------------------------------------- */
const saveMutation = useMutation({
  mutationFn: body => axios.post('/erp/signatures', body).then(r => r.data),
  onSuccess() {
    showToast('Signatures saved!', 'success')
    reset()
  },
  onError(e) {
    showToast(e.response?.data?.message ?? 'Upload failed — try again.', 'danger')
  }
})

function validateForm() {
  errors.value = {
    selectedMaid: '',
    selectedCustomer: '',
    note: ''
  }

  let hasError = false

  if (!selectedMaid.value) {
    errors.value.selectedMaid = 'Maid is required'
    hasError = true
  }

  if (!selectedCustomer.value) {
    errors.value.selectedCustomer = 'Customer is required'
    hasError = true
  }

  if (!note.value.trim()) {
    errors.value.note = 'Note is required'
    hasError = true
  }

  return !hasError
}

async function handleSubmit() {
  if (!validateForm()) {
    showToast('Please fill in all required fields.', 'warning')
    return
  }

  const img = grabPadImage(staffPad)
  if (!img) {
    showToast('Please sign as staff.', 'warning')
    return
  }

  state.staff = img

  saveMutation.mutate({
    customer_signature: state.customer,
    staff_signature: state.staff,
    maid_name: selectedMaid.value,
    customer_name: selectedCustomer.value,
    note: note.value.trim()
  })
}
</script>

<template>
  <div class="signature-container">
    <!-- Toast Notifications -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
      <div v-for="(t, index) in [toast].filter(t => t.show)" :key="index" 
           class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header" :class="{
          'bg-success text-white': t.type === 'success',
          'bg-warning text-dark': t.type === 'warning',
          'bg-danger text-white': t.type === 'danger',
          'bg-info text-white': t.type === 'info'
        }">
          <i class="ki-duotone me-2" :class="{
            'ki-check-circle': t.type === 'success',
            'ki-information': t.type === 'warning' || t.type === 'info',
            'ki-cross-circle': t.type === 'danger'
          }">
            <i class="path1"></i>
            <i class="path2"></i>
          </i>
          <strong class="me-auto">{{ t.type.charAt(0).toUpperCase() + t.type.slice(1) }}</strong>
          <button type="button" class="btn-close btn-close-white" @click="t.show = false" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          {{ t.message }}
        </div>
      </div>
    </div>

    <!-- Form Card -->
    <div class="card signature-card shadow-sm border-0 mx-auto mt-4" style="max-width: 820px;">
      <div class="card-header">
        <h4 class="mb-0 fw-bold d-flex align-items-center">
          <i class="ki-duotone ki-pencil fs-1 me-2 text-primary">
            <i class="path1"></i>
            <i class="path2"></i>
          </i>
          Signature for Passport
        </h4>
      </div>

      <div class="card-body p-4">
        <form @submit.prevent="handleSubmit">
          <!-- Select Maid -->
          <div class="mb-3">
            <label class="form-label fw-semibold">
              Select Maid <span class="text-danger">*</span>
            </label>
            <RemoteSelect
              v-model="selectedMaid"
              name="maid_name"
              api-url="/all/maids"
              placeholder="Select a maid"
              value-key="id"
            
            />
            <div v-if="errors.selectedMaid" class="text-danger small mt-1">{{ errors.selectedMaid }}</div>
          </div>

          <!-- Select Customer -->
          <div class="mb-3">
            <label class="form-label fw-semibold">
              Select Customer <span class="text-danger">*</span>
            </label>
            <RemoteSelect
              v-model="selectedCustomer"
              name="customer_name"
              api-url="/all-customers"
              placeholder="Select a customer"
              value-key="id"
            />
            <div v-if="errors.selectedCustomer" class="text-danger small mt-1">{{ errors.selectedCustomer }}</div>
          </div>

          <!-- Note Input -->
          <div class="mb-4">
            <label class="form-label fw-semibold">
              Note <span class="text-danger">*</span>
            </label>
            <textarea
              v-model="note"
              class="form-control"
              rows="2"
              name="note"
              placeholder="Write any relevant note here"
            ></textarea>
            <div v-if="errors.note" class="text-danger small mt-1">{{ errors.note }}</div>
          </div>

          <!-- Signature Steps -->
          <template v-if="state.step === 1">
            <div class="mb-4">
              <label class="form-label fw-semibold d-flex align-items-center">
                <i class="ki-duotone ki-user fs-3 me-2 text-info">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
                Customer Signature
              </label>
              <div class="signature-pad-wrapper border rounded">
                <VueSignaturePad
                  ref="customerPad"
                  height="400px"
                  width="100%"
                  :maxWidth="2"
                  :minWidth="2"
                />
              </div>
              <button 
                type="button" 
                class="btn btn-outline-secondary btn-sm mt-2" 
                @click="handleClearCustomer"
              >
                <i class="ki-duotone ki-trash fs-4 me-1">
                  <i class="path1"></i>
                  <i class="path2"></i>
                  <i class="path3"></i>
                  <i class="path4"></i>
                  <i class="path5"></i>
                </i>
                Clear
              </button>
            </div>

            <button type="button" class="btn btn-primary" @click="nextStep">
              Next
              <i class="ki-duotone ki-arrow-right fs-3 ms-1">
                <i class="path1"></i>
                <i class="path2"></i>
              </i>
            </button>
          </template>

          <template v-else>
            <div class="mb-4">
              <label class="form-label fw-semibold d-flex align-items-center">
                <i class="ki-duotone ki-profile-user fs-3 me-2 text-success">
                  <i class="path1"></i>
                  <i class="path2"></i>
                  <i class="path3"></i>
                  <i class="path4"></i>
                </i>
                Staff Signature
              </label>
              <div class="signature-pad-wrapper border rounded">
                <VueSignaturePad
                  ref="staffPad"
                  height="400px"
                  width="100%"
                  :maxWidth="2"
                  :minWidth="2"
                />
              </div>
              <button 
                type="button" 
                class="btn btn-outline-secondary btn-sm mt-2" 
                @click="handleClearStaff"
              >
                <i class="ki-duotone ki-trash fs-4 me-1">
                  <i class="path1"></i>
                  <i class="path2"></i>
                  <i class="path3"></i>
                  <i class="path4"></i>
                  <i class="path5"></i>
                </i>
                Clear
              </button>
            </div>

            <div class="d-flex justify-content-between">
              <button type="button" class="btn btn-secondary" @click="state.step = 1">
                <i class="ki-duotone ki-arrow-left fs-3 me-1">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
                Back
              </button>
              <button 
                type="button" 
                class="btn btn-primary" 
                :disabled="saveMutation.isPending.value" 
                @click="handleSubmit"
              >
                <span v-if="saveMutation.isPending.value" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="ki-duotone ki-check fs-3 me-1">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
                Submit
              </button>
            </div>
          </template>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Theme-adaptive styling using Bootstrap CSS variables */
.signature-container {
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  padding: 1.5rem;
}

.signature-card {
  background-color: var(--bs-card-bg);
  border-color: var(--bs-border-color);
}

.card-header {
  background-color: var(--bs-body-bg);
  border-bottom: 1px solid var(--bs-border-color);
  padding: 1.25rem 1.5rem;
}

.card-body {
  background-color: var(--bs-card-bg);
}

/* Form controls */
.form-control,
.form-select {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
  color: var(--bs-body-color);
}

.form-control:focus,
.form-select:focus {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-primary);
  color: var(--bs-body-color);
}

/* Signature pad wrapper */
.signature-pad-wrapper {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color) !important;
  overflow: hidden;
}

/* Dark mode adjustments */
[data-bs-theme="dark"] .signature-pad-wrapper {
  background-color: #1a1a1a;
}
</style>
