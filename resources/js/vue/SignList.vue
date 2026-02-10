<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import axios from 'axios'
import { useQuery } from '@tanstack/vue-query'

// 🧭 Pagination & search state
const currentPage = ref(1)
const pageSize = ref(10)
const searchQuery = ref('')

// 📡 API fetch function
const fetchSignatures = async () => {
  const { data } = await axios.get('/signatures/data-table', {
    params: {
      page: currentPage.value,
      per_page: pageSize.value,
      search: searchQuery.value
    }
  })
  return data
}

// ✅ useQuery with proper key
const { data, isPending, isError, error, refetch } = useQuery({
  queryKey: ['signatures', currentPage, searchQuery],
  queryFn: fetchSignatures,
  keepPreviousData: true
})

// 🔁 Watch for changes and update URL
watch([currentPage, searchQuery, pageSize], () => {
  refetch()
  updateSearchURL()
})

// 🌐 Update URL based on state
function updateSearchURL() {
  const params = new URLSearchParams()

  if (searchQuery.value) params.set('search', searchQuery.value)
  if (currentPage.value) params.set('page', currentPage.value)
  if (pageSize.value) params.set('perPage', pageSize.value)

  const newUrl = `${window.location.pathname}?${params.toString()}`
  window.history.pushState(null, '', newUrl)
}

// 📥 Sync from URL on page load
onMounted(() => {
  const params = new URLSearchParams(window.location.search)

  if (params.get('search')) searchQuery.value = params.get('search')
  if (params.get('page')) currentPage.value = parseInt(params.get('page'))
  if (params.get('perPage')) pageSize.value = parseInt(params.get('perPage'))
})

// Smart pagination
const totalPages = computed(() => {
  if (!data.value?.total || !pageSize.value) return 1
  return Math.ceil(data.value.total / pageSize.value)
})

const visiblePages = computed(() => {
  const current = currentPage.value
  const total = totalPages.value
  const maxVisible = 5

  if (total <= maxVisible + 2) {
    return Array.from({ length: total }, (_, i) => i + 1)
  }

  const halfVisible = Math.floor(maxVisible / 2)
  let start = Math.max(1, current - halfVisible)
  let end = Math.min(total, current + halfVisible)

  if (current <= halfVisible + 1) {
    start = 1
    end = maxVisible
  }

  if (current >= total - halfVisible) {
    start = total - maxVisible + 1
    end = total
  }

  return Array.from({ length: end - start + 1 }, (_, i) => start + i)
})
</script>

<template>
  <div class="sign-container">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <h2 class="h4 fw-bold mb-0 d-flex align-items-center">
        <i class="ki-duotone ki-pencil fs-1 me-2 text-primary">
          <i class="path1"></i>
          <i class="path2"></i>
        </i>
        Signature Records
      </h2>
    </div>

    <!-- Search Card -->
    <div class="card sign-card mb-4 border-0 shadow-sm">
      <div class="card-body p-4">
        <div class="row g-3 align-items-end">
          <!-- Search -->
          <div class="col-12">
            <label class="form-label fw-semibold text-muted small text-uppercase mb-1">Search</label>
            <div class="input-group">
              <span class="input-group-text border-end-0">
                <i class="ki-duotone ki-magnifier fs-1">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
              </span>
              <input
                v-model="searchQuery"
                type="text"
                class="form-control border-start-0 ps-0"
                placeholder="Search by customer or maid name..."
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="card sign-card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover sign-table mb-0">
            <thead>
              <tr>
                <th style="width: 60px">
                  <i class="ki-duotone ki-badge fs-5 me-1 text-primary">
                    <i class="path1"></i>
                    <i class="path2"></i>
                    <i class="path3"></i>
                    <i class="path4"></i>
                    <i class="path5"></i>
                  </i>
                  ID
                </th>
                <th>
                  <i class="ki-duotone ki-profile-user fs-5 me-1 text-success">
                    <i class="path1"></i>
                    <i class="path2"></i>
                    <i class="path3"></i>
                    <i class="path4"></i>
                  </i>
                  Customer
                </th>
                <th>
                  <i class="ki-duotone ki-user fs-5 me-1 text-info">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Maid
                </th>
                <th>
                  <i class="ki-duotone ki-note-2 fs-5 me-1 text-warning">
                    <i class="path1"></i>
                    <i class="path2"></i>
                    <i class="path3"></i>
                    <i class="path4"></i>
                  </i>
                  Note
                </th>
                <th>Customer Sign</th>
                <th>Staff Sign</th>
                <th>Created By</th>
                <th>Created At</th>
              </tr>
            </thead>
            <tbody>
              <!-- Skeleton loader -->
              <template v-if="isPending">
                <tr v-for="i in pageSize" :key="`skeleton-${i}`" class="skeleton-row">
                  <td><div class="skeleton skeleton-text" style="width: 40px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 70%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 70%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 80%"></div></td>
                  <td><div class="skeleton skeleton-image" style="width: 100px; height: 60px"></div></td>
                  <td><div class="skeleton skeleton-image" style="width: 100px; height: 60px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 60%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 70%"></div></td>
                </tr>
              </template>

              <!-- Empty state -->
              <tr v-else-if="!isPending && data && data.data && !data.data.length">
                <td colspan="8" class="text-center text-muted py-4">
                  <i class="ki-duotone ki-inbox fs-1 d-block mb-2">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  No signature records found.
                </td>
              </tr>

              <!-- Actual data -->
              <tr v-else-if="data && data.data" v-for="row in data.data" :key="row.id">
                <td>{{ row.id }}</td>
                <td>{{ row.customer_name }}</td>
                <td>{{ row.maid_name }}</td>
                <td>{{ row.note }}</td>
                <td>
                  <img 
                    v-if="row.customer_signature_url" 
                    :src="row.customer_signature_url" 
                    alt="Customer signature"
                    class="signature-image"
                    style="max-width: 100px; height: auto;"
                  />
                  <span v-else class="text-muted">—</span>
                </td>
                <td>
                  <img 
                    v-if="row.staff_signature_url" 
                    :src="row.staff_signature_url" 
                    alt="Staff signature"
                    class="signature-image"
                    style="max-width: 100px; height: auto;"
                  />
                  <span v-else class="text-muted">—</span>
                </td>
                <td>{{ row.created_by }}</td>
                <td>{{ row.created_at }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination Footer -->
      <div v-if="isPending || (data && data.total > 0)" class="card-footer sign-footer">
        <nav class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <!-- Pagination controls -->
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <a class="page-link" href="#" @click.prevent="currentPage > 1 && (currentPage = currentPage - 1)">
                <i class="ki-duotone ki-left fs-5">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
                Previous
              </a>
            </li>

            <li v-if="visiblePages[0] > 1" class="page-item">
              <a class="page-link" href="#" @click.prevent="currentPage = 1">1</a>
            </li>

            <li v-if="visiblePages[0] > 2" class="page-item disabled">
              <span class="page-link">...</span>
            </li>

            <li v-for="pageNum in visiblePages" :key="pageNum" class="page-item" :class="{ active: pageNum === currentPage }">
              <a class="page-link" href="#" @click.prevent="currentPage = pageNum">{{ pageNum }}</a>
            </li>

            <li v-if="visiblePages[visiblePages.length - 1] < totalPages - 1" class="page-item disabled">
              <span class="page-link">...</span>
            </li>

            <li v-if="visiblePages[visiblePages.length - 1] < totalPages" class="page-item">
              <a class="page-link" href="#" @click.prevent="currentPage = totalPages">{{ totalPages }}</a>
            </li>

            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <a class="page-link" href="#" @click.prevent="currentPage < totalPages && (currentPage = currentPage + 1)">
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
            <i class="ki-duotone ki-element-11 fs-5 me-1">
              <i class="path1"></i>
              <i class="path2"></i>
              <i class="path3"></i>
              <i class="path4"></i>
            </i>
            Total Results: <strong class="text-primary">{{ data?.total || 0 }}</strong>
          </div>
        </nav>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Theme-adaptive styling using Bootstrap CSS variables */
.sign-container {
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  margin: 1.5rem 3rem;
}

.sign-card {
  background-color: var(--bs-card-bg);
  border-color: var(--bs-border-color);
}

.sign-table {
  color: var(--bs-body-color);
}

.sign-table thead {
  background-color: var(--bs-body-bg);
}

.sign-table thead th {
  padding: 1rem;
  font-weight: 600;
  color: var(--bs-emphasis-color);
  white-space: nowrap;
  font-size: 0.85rem;
  border-bottom: 2px solid var(--bs-border-color);
}

.sign-table th:first-child,
.sign-table td:first-child {
  padding-left: 1.25rem;
}

.sign-table tbody td {
  padding: 0.75rem 1rem;
  vertical-align: middle;
  border-bottom: 1px solid var(--bs-border-color);
}

.sign-table tbody tr:hover {
  background-color: var(--bs-tertiary-bg);
}

.sign-footer {
  background-color: var(--bs-body-bg);
  border-top: 1px solid var(--bs-border-color);
  padding: 1rem 1.5rem;
}

/* Form controls */
.form-control {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
  color: var(--bs-body-color);
}

.form-control:focus {
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

/* Signature images */
.signature-image {
  border: 1px solid var(--bs-border-color);
  border-radius: 0.25rem;
  padding: 0.25rem;
  background-color: var(--bs-body-bg);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .sign-container {
    margin: 1rem;
  }

  .sign-table thead th,
  .sign-table tbody td {
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
}

.skeleton-text {
  height: 1.25rem;
}

.skeleton-image {
  border-radius: 0.25rem;
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
