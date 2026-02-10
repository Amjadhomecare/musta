<template>
  <div class="attachment-container">
    <!-- Filter Card -->
    <div class="card attachment-card mb-4 border-0 shadow-sm">
      <div class="card-body p-4">
        <div class="row g-4 align-items-end">
          <!-- Search -->
          <div class="col-md-6">
            <label class="form-label fw-semibold text-muted small text-uppercase mb-1">Maid Name</label>
            <div class="input-group">
              <span class="input-group-text border-end-0">
                <i class="ki-duotone ki-magnifier fs-1">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
              </span>
              <input
                type="text"
                v-model="maidSearch"
                class="form-control border-start-0 ps-0"
                placeholder="Search by name..."
                @input="searchMaids"
              />
            </div>
          </div>

          <!-- Status -->
          <div class="col-md-6">
            <label class="form-label fw-semibold text-muted small text-uppercase mb-1">Status</label>
            <div class="input-group">
              <span class="input-group-text border-end-0">
                <i class="ki-duotone ki-information-3 fs-1">
                  <i class="path1"></i>
                  <i class="path2"></i>
                  <i class="path3"></i>
                </i>
              </span>
              <select
                v-model="status"
                class="form-select border-start-0 ps-0"
                @change="searchMaids"
              >
                <option value="">All</option>
                <option value="approved">Approved</option>
                <option value="hired">Hired</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="card attachment-card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover attachment-table mb-0">
            <thead>
              <tr>
                <th>
                  <i class="ki-duotone ki-user fs-5 me-1 text-primary">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Maid Name
                </th>
                <th>
                  <i class="ki-duotone ki-notepad fs-5 me-1 text-info">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Note
                </th>
                <th>
                  <i class="ki-duotone ki-shield-tick fs-5 me-1 text-success">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Status
                </th>
                <th>
                  <i class="ki-duotone ki-file fs-5 me-1 text-warning">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  File Name
                </th>
                <th>
                  <i class="ki-duotone ki-document fs-5 me-1 text-secondary">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  File Type
                </th>
                <th>
                  <i class="ki-duotone ki-calendar fs-5 me-1 text-muted">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Created At
                </th>
                <th style="width: 120px">
                  <i class="ki-duotone ki-setting-2 fs-5 me-1">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Actions
                </th>
              </tr>
            </thead>
            <tbody>
              <!-- Skeleton loader -->
              <template v-if="isValidating">
                <tr v-for="i in 10" :key="`skeleton-${i}`" class="skeleton-row">
                  <td><div class="skeleton skeleton-text" style="width: 70%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 80%"></div></td>
                  <td><div class="skeleton skeleton-badge" style="width: 70px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 75%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 60%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 65%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 80px"></div></td>
                </tr>
              </template>

              <!-- Empty state -->
              <tr v-else-if="!isValidating && data && data.data && !data.data.length">
                <td colspan="7" class="text-center text-muted py-4">
                  <i class="ki-duotone ki-inbox fs-1 d-block mb-2">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  No maids found.
                </td>
              </tr>

              <!-- Actual data -->
              <tr v-else-if="data && data.data" v-for="row in data.data" :key="row.id">
                <td>
                  <a
                    :href="`/maid-report/${encodeURIComponent(row.maidInfo?.name || '')}`"
                    target="_blank"
                    class="text-decoration-none"
                  >
                    {{ row.maidInfo?.name || 'N/A' }}
                  </a>
                </td>
                <td>{{ row.note }}</td>
                <td>
                  <span
                    class="badge"
                    :class="{
                      'bg-success': row.maidInfo?.maid_status?.toLowerCase() === 'approved' || row.maidInfo?.maid_status?.toLowerCase() === 'active',
                      'bg-primary': row.maidInfo?.maid_status?.toLowerCase() === 'hired',
                      'bg-warning text-dark': row.maidInfo?.maid_status?.toLowerCase() === 'pending',
                      'bg-secondary': !row.maidInfo?.maid_status
                    }"
                  >
                    {{ row.maidInfo?.maid_status || 'N/A' }}
                  </span>
                </td>
                <td>
                  <a :href="row.file_path" target="_blank" class="text-decoration-none">
                    {{ row.file_name }}
                  </a>
                </td>
                <td>{{ row.file_type }}</td>
                <td>{{ formatDate(row.created_at) }}</td>
                <td>
                  <button
                    type="button"
                    class="btn btn-danger btn-sm"
                    @click="deleteMaidAttachment(row.id)"
                  >
                    <i class="ki-duotone ki-trash fs-4">
                      <i class="path1"></i>
                      <i class="path2"></i>
                      <i class="path3"></i>
                      <i class="path4"></i>
                      <i class="path5"></i>
                    </i>
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination Footer -->
      <div v-if="isValidating || (data?.meta && data.meta.total > 0)" class="card-footer attachment-footer">
        <nav class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <!-- Pagination controls -->
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item" :class="{ disabled: page === 1 }">
              <a
                class="page-link"
                href="#"
                @click.prevent="page > 1 && changePage(page - 1)"
              >
                <i class="ki-duotone ki-left fs-5">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
                Previous
              </a>
            </li>

            <li v-if="visiblePages[0] > 1" class="page-item">
              <a class="page-link" href="#" @click.prevent="changePage(1)">
                1
              </a>
            </li>

            <li v-if="visiblePages[0] > 2" class="page-item disabled">
              <span class="page-link">...</span>
            </li>

            <li
              v-for="pageNum in visiblePages"
              :key="pageNum"
              class="page-item"
              :class="{ active: pageNum === page }"
            >
              <a class="page-link" href="#" @click.prevent="changePage(pageNum)">
                {{ pageNum }}
              </a>
            </li>

            <li v-if="visiblePages[visiblePages.length - 1] < totalPages - 1" class="page-item disabled">
              <span class="page-link">...</span>
            </li>

            <li v-if="visiblePages[visiblePages.length - 1] < totalPages" class="page-item">
              <a class="page-link" href="#" @click.prevent="changePage(totalPages)">
                {{ totalPages }}
              </a>
            </li>

            <li class="page-item" :class="{ disabled: page === totalPages }">
              <a
                class="page-link"
                href="#"
                @click.prevent="page < totalPages && changePage(page + 1)"
              >
                Next
                <i class="ki-duotone ki-right fs-5">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
              </a>
            </li>
          </ul>

          <!-- Page info -->
          <div class="text-muted small">
            Page <strong>{{ page }}</strong> of 
            <strong>{{ totalPages }}</strong>
            <span class="mx-2">•</span>
            <strong class="text-primary">{{ data?.meta?.total || 0 }}</strong> total
          </div>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted } from 'vue'
import {
  useMaidAttachDataTable,
  useDeleteMaidAttachment
} from '../hooks/useMaidAttachment'

const {
  maidSearch,
  status,
  data,
  isValidating,
  page,
  searchMaids,
  changePage,
  mutate
} = useMaidAttachDataTable()

const { deleteMaidAttachment } = useDeleteMaidAttachment()

// Listen for upload events to refresh the table
const handleUploadEvent = () => {
  mutate()
}

onMounted(() => {
  window.addEventListener('maid-attachment-uploaded', handleUploadEvent)
})

onUnmounted(() => {
  window.removeEventListener('maid-attachment-uploaded', handleUploadEvent)
})

const totalPages = computed(() => {
  if (!data.value?.meta) return 1
  return Math.ceil(data.value.meta.total / data.value.meta.per_page)
})

// Smart pagination: show only a limited range of page numbers
const visiblePages = computed(() => {
  const current = page.value
  const total = totalPages.value
  const maxVisible = 5 // Maximum number of page buttons to show (excluding first/last)
  
  if (total <= maxVisible + 2) {
    // If total pages is small, show all pages
    return Array.from({ length: total }, (_, i) => i + 1)
  }
  
  const halfVisible = Math.floor(maxVisible / 2)
  let start = Math.max(1, current - halfVisible)
  let end = Math.min(total, current + halfVisible)
  
  // Adjust if we're near the start
  if (current <= halfVisible + 1) {
    start = 1
    end = maxVisible
  }
  
  // Adjust if we're near the end
  if (current >= total - halfVisible) {
    start = total - maxVisible + 1
    end = total
  }
  
  return Array.from({ length: end - start + 1 }, (_, i) => start + i)
})

const formatDate = (value: string | number | Date) => {
  if (!value) return '—'
  const d = new Date(value)
  return isNaN(d.getTime()) ? '—' : d.toLocaleString()
}
</script>

<style scoped>
/* Theme-adaptive styling using Bootstrap CSS variables */
.attachment-container {
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  margin: 1.5rem 3rem;
}

.attachment-card {
  background-color: var(--bs-card-bg);
  border-color: var(--bs-border-color);
}

.attachment-table {
  color: var(--bs-body-color);
}

.attachment-table thead {
  background-color: var(--bs-body-bg);
}

.attachment-table thead th {
  padding: 1rem;
  font-weight: 600;
  color: var(--bs-emphasis-color);
  white-space: nowrap;
  font-size: 0.85rem;
  border-bottom: 2px solid var(--bs-border-color);
}

/* Add extra padding to first column */
.attachment-table th:first-child,
.attachment-table td:first-child {
  padding-left: 1.25rem;
}

.attachment-table tbody td {
  padding: 0.75rem 1rem;
  vertical-align: middle;
  border-bottom: 1px solid var(--bs-border-color);
}

.attachment-table tbody tr:hover {
  background-color: var(--bs-tertiary-bg);
}

.attachment-footer {
  background-color: var(--bs-body-bg);
  border-top: 1px solid var(--bs-border-color);
  padding: 1rem 1.5rem;
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

/* Badge adjustments */
.badge {
  font-weight: 600;
  padding: 0.35rem 0.65rem;
  font-size: 0.75rem;
}

/* Link hover effects */
a.text-primary:hover {
  text-decoration: underline !important;
}

/* Custom scrollbar for table */
.table-responsive::-webkit-scrollbar {
  height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
  background: var(--bs-secondary-bg);
}

.table-responsive::-webkit-scrollbar-thumb {
  background: var(--bs-border-color);
  border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
  background: var(--bs-secondary-color);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .attachment-table thead th,
  .attachment-table tbody td {
    padding: 0.5rem;
    font-size: 0.85rem;
  }
}

/* Skeleton Loader Styles */
.skeleton {
  background: linear-gradient(
    90deg,
    var(--bs-tertiary-bg) 0%,
    var(--bs-secondary-bg) 50%,
    var(--bs-tertiary-bg) 100%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 0.25rem;
  height: 1.63rem;
}

.skeleton-text {
  height: 1.63rem;
}

.skeleton-badge {
  height: 1.63rem;
  border-radius: 0.4rem;
}

.skeleton-row {
  opacity: 0.7;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}
</style>
