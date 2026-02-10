<template>
  <div class="upload-container">
    <!--begin::Upload card-->
    <div class="card upload-card shadow-sm mb-4 border-0">
      <div class="card-header py-3">
        <h5 class="card-title mb-0 d-flex align-items-center">
          <i class="ki-duotone ki-file-up fs-1 me-2 text-primary">
            <i class="path1"></i>
            <i class="path2"></i>
          </i>
          Upload Maid Attachment
        </h5>
      </div>
      <div class="card-body p-4">
        <form @submit.prevent="handleSubmit" class="row gx-5 gy-4">
          <!-- Maid select -->
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold text-muted small text-uppercase mb-1">Maid Name</label>
            <div class="input-group">
              <span class="input-group-text border-end-0">
                <i class="ki-duotone ki-user fs-1">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
              </span>
              <input
                v-model="maidSearch"
                type="text"
                class="form-control border-start-0 ps-0"
                placeholder="Search for a maid (min 3 chars)..."
                @input="debounceFetchMaids(maidSearch)"
                :disabled="!!maidName"
              />
              <button
                v-if="maidName"
                type="button"
                class="btn btn-outline-secondary"
                @click="clearMaidSelection"
              >
                <i class="ki-duotone ki-cross fs-3">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
              </button>
            </div>
            
            <!-- Maid dropdown list -->
            <div v-if="maids.length > 0 && !maidName" class="position-relative">
              <div class="dropdown-menu show w-100 mt-1 shadow" style="max-height: 200px; overflow-y: auto;">
                <button
                  v-for="maid in maids"
                  :key="maid.id"
                  type="button"
                  class="dropdown-item d-flex align-items-center py-2"
                  @click="selectMaidFromList(maid)"
                >
                  <i class="ki-duotone ki-user fs-3 me-2 text-primary">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  <span>{{ maid.text || maid.name }}</span>
                </button>
              </div>
            </div>
            
            <!-- Selected maid display -->
            <div v-if="maidName" class="mt-2">
              <span class="badge bg-primary fs-6">
                <i class="ki-duotone ki-check fs-5 me-1">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
                {{ maidName }}
              </span>
            </div>
            
            <small v-if="error" class="text-danger d-block mt-1">{{ error }}</small>
            <small v-if="loading" class="text-muted d-block mt-1">
              <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Searching...
            </small>
          </div>

          <!-- Note -->
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold text-muted small text-uppercase mb-1">Note</label>
            <textarea
              v-model="note"
              class="form-control"
              rows="3"
              placeholder="Enter note..."
            ></textarea>
          </div>

          <!-- File upload -->
          <div class="col-12">
            <label class="form-label fw-semibold text-muted small text-uppercase mb-1">File</label>
            <div class="upload-zone border-2 border-dashed rounded p-4 text-center" :class="{ 'upload-dragging': isDragging }">
              <input
                ref="fileInput"
                type="file"
                class="d-none"
                @change="onFileChange"
              />
              <div @click="$refs.fileInput.click()" @dragover.prevent="isDragging = true" @dragleave="isDragging = false" @drop.prevent="onFileDrop" style="cursor: pointer;">
                <i class="ki-duotone ki-cloud-add fs-3x text-primary mb-3">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
                <p class="mb-0 fw-semibold">Drag file here or <span class="text-primary">click to upload</span></p>
                <p class="text-muted small mb-0">Select a file to attach to this maid</p>
              </div>
              
              <!-- Selected file display -->
              <div v-if="fileList.length > 0" class="mt-3 pt-3 border-top">
                <div v-for="(file, index) in fileList" :key="index" class="d-flex align-items-center justify-content-between p-2 bg-body-secondary rounded">
                  <div class="d-flex align-items-center">
                    <i class="ki-duotone ki-file fs-2 text-success me-2">
                      <i class="path1"></i>
                      <i class="path2"></i>
                    </i>
                    <span class="fw-semibold">{{ file.name }}</span>
                    <span class="text-muted ms-2">({{ formatFileSize(file.size) }})</span>
                  </div>
                  <button type="button" class="btn btn-sm btn-light" @click="removeFile(index)">
                    <i class="ki-duotone ki-cross fs-3">
                      <i class="path1"></i>
                      <i class="path2"></i>
                    </i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Progress -->
          <div v-if="uploading" class="col-12">
            <div class="progress" style="height: 25px;">
              <div
                class="progress-bar progress-bar-striped progress-bar-animated"
                role="progressbar"
                :style="{ width: uploadProgress + '%' }"
                :aria-valuenow="uploadProgress"
                aria-valuemin="0"
                aria-valuemax="100"
              >
                {{ uploadProgress }}%
              </div>
            </div>
          </div>

          <!-- Submit -->
          <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary" :disabled="loading || uploading">
              <span v-if="loading || uploading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              <i v-else class="ki-duotone ki-cloud-add me-2">
                <i class="path1"></i>
                <i class="path2"></i>
              </i>
              {{ uploading ? 'Uploading...' : 'Upload' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Feedback Toast -->
    <div v-if="message" class="position-fixed top-0 end-0 p-3" style="z-index: 11">
      <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header" :class="success ? 'bg-success text-white' : 'bg-danger text-white'">
          <i class="ki-duotone me-2" :class="success ? 'ki-check-circle' : 'ki-cross-circle'">
            <i class="path1"></i>
            <i class="path2"></i>
          </i>
          <strong class="me-auto">{{ success ? 'Success' : 'Error' }}</strong>
          <button type="button" class="btn-close btn-close-white" @click="message = ''" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          {{ message }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useMaidAttachment } from '/resources/js/hooks/useMaidAttachment'

const {
  maidSearch,
  maidName,
  maids,
  note,
  file,
  fileList,
  message,
  success,
  loading,
  error,
  uploading,
  uploadProgress,
  debounceFetchMaids,
  selectMaid,
  handleFileUpload,
  uploadAttachment
} = useMaidAttachment()

const fileInput = ref(null)
const isDragging = ref(false)

const selectMaidFromList = (maid) => {
  console.log('Selected maid object:', maid) // Debug: see what we have
  
  // Extract the clean name - if text has " / ", take only the part before it
  let cleanName = maid.name
  if (!cleanName && maid.text) {
    // If text is like "TEST MAID / UAE ID: xxx", extract just "TEST MAID"
    cleanName = maid.text.split(' / ')[0].trim()
  }
  
  // Use the clean name for the backend (exact database match)
  maidName.value = cleanName
  // Show the full text in the search field for user clarity
  maidSearch.value = maid.text || maid.name
  // Clear the dropdown
  maids.value = []
  
  console.log('Set maidName to:', cleanName) // Debug: confirm what we set
}

const clearMaidSelection = () => {
  maidName.value = ''
  maidSearch.value = ''
  maids.value = []
}

const onFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    handleFileUpload({ raw: file, name: file.name, size: file.size })
  }
}

const onFileDrop = (event) => {
  isDragging.value = false
  const file = event.dataTransfer.files[0]
  if (file) {
    handleFileUpload({ raw: file, name: file.name, size: file.size })
  }
}

const removeFile = (index) => {
  // Clear the file by calling handleFileUpload with null
  handleFileUpload(null)
  // Reset the file input
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const handleSubmit = async () => {
  console.log('Form submitted - Component state:', {
    maidName: maidName.value,
    note: note.value,
    fileList: fileList.value
  })
  
  // Check what the hook actually sees
  console.log('Hook validation check:', {
    'file exists': !!file?.value,
    'maidName exists': !!maidName.value,
    'note exists': !!note.value
  })
  
  await uploadAttachment()
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}
</script>

<style scoped>
/* Theme-adaptive styling using Bootstrap CSS variables */
.upload-container {
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  margin: 1.5rem 3rem;
}

.upload-card {
  background-color: var(--bs-card-bg);
  border-color: var(--bs-border-color);
}

.card-header {
  background-color: var(--bs-body-bg);
  border-bottom: 1px solid var(--bs-border-color);
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

.input-group-text {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
  color: var(--bs-body-color);
}

/* Darker background for icon containers in dark mode */
[data-bs-theme="dark"] .input-group-text {
  background-color: #0d0d14;
}

/* Upload zone */
.upload-zone {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
  transition: all 0.3s ease;
}

.upload-zone:hover {
  border-color: var(--bs-primary);
  background-color: var(--bs-tertiary-bg);
}

.upload-dragging {
  border-color: var(--bs-primary) !important;
  background-color: var(--bs-primary-bg-subtle) !important;
}

/* Dropdown */
.dropdown-menu {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
}

.dropdown-item {
  color: var(--bs-body-color);
}

.dropdown-item:hover {
  background-color: var(--bs-tertiary-bg);
  color: var(--bs-body-color);
}

/* Badge adjustments */
.badge {
  font-weight: 600;
  padding: 0.35rem 0.65rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .upload-container {
    margin: 1rem;
  }
}
</style>
