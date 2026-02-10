<script setup>
import { computed } from 'vue'
import { usePaginationQuery } from '@/composables/usePagination'

// Pagination Composable
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
  apiUrl: '/get-maids-salary-p1',
  queryKeyPrefix: 'salary-maid-p1',
})

// Smart pagination
const totalPages = computed(() => {
  if (!total.value || !pageSize.value) return 1
  return Math.ceil(total.value / pageSize.value)
})

const visiblePages = computed(() => {
  const current = currentPage.value
  const totalPagesValue = totalPages.value
  const maxVisible = 5

  if (totalPagesValue <= maxVisible + 2) {
    return Array.from({ length: totalPagesValue }, (_, i) => i + 1)
  }

  const halfVisible = Math.floor(maxVisible / 2)
  let start = Math.max(1, current - halfVisible)
  let end = Math.min(totalPagesValue, current + halfVisible)

  if (current <= halfVisible + 1) {
    start = 1
    end = maxVisible
  }

  if (current >= totalPagesValue - halfVisible) {
    start = totalPagesValue - maxVisible + 1
    end = totalPagesValue
  }

  return Array.from({ length: end - start + 1 }, (_, i) => start + i)
})
</script>

<template>
  <div class="salary-container">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <h2 class="h4 fw-bold mb-0 d-flex align-items-center">
        <i class="ki-duotone ki-dollar fs-1 me-2 text-primary">
          <i class="path1"></i>
          <i class="path2"></i>
          <i class="path3"></i>
        </i>
        Salary Maid P1
      </h2>
    </div>

    <!-- Controls Toolbar -->
    <div class="card salary-card mb-4 border-0 shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row align-items-md-end gap-3">
          <!-- Search -->
          <div class="flex-grow-1">
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
                placeholder="Search maid name…"
                @input="refetch"
              />
            </div>
          </div>

          <!-- Actions -->
          <div class="d-flex gap-2">
            <button 
              v-if="data && data.data && data.data.length > 0"
              type="button" 
              class="btn btn-success d-flex align-items-center" 
              @click="exportToExcel"
            >
              <i class="ki-duotone ki-file-down fs-2 me-2">
                <i class="path1"></i>
                <i class="path2"></i>
              </i>
              Export to Excel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="card salary-card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover salary-table mb-0">
            <thead>
              <tr>
                <th>
                  <i class="ki-duotone ki-calendar fs-5 me-1 text-primary">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Date
                </th>
                <th>
                  <i class="ki-duotone ki-barcode fs-5 me-1 text-info">
                    <i class="path1"></i>
                    <i class="path2"></i>
                    <i class="path3"></i>
                    <i class="path4"></i>
                    <i class="path5"></i>
                    <i class="path6"></i>
                    <i class="path7"></i>
                    <i class="path8"></i>
                  </i>
                  Ref Code
                </th>
                <th>
                  <i class="ki-duotone ki-user fs-5 me-1 text-success">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Maid Name
                </th>
                <th>
                  <i class="ki-duotone ki-document fs-5 me-1 text-warning">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Statement
                </th>
                <th>
                  <i class="ki-duotone ki-dollar fs-5 me-1 text-danger">
                    <i class="path1"></i>
                    <i class="path2"></i>
                    <i class="path3"></i>
                  </i>
                  Amount
                </th>
              </tr>
            </thead>
            <tbody>
              <!-- Skeleton loader -->
              <template v-if="isLoading">
                <tr v-for="i in pageSize" :key="`skeleton-${i}`" class="skeleton-row">
                  <td><div class="skeleton skeleton-text" style="width: 100px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 80px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 70%"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 60px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 60px"></div></td>
                </tr>
              </template>

              <!-- Empty state -->
              <tr v-else-if="!isLoading && data && data.data && !data.data.length">
                <td colspan="5" class="text-center text-muted py-4">
                  <i class="ki-duotone ki-inbox fs-1 d-block mb-2">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  No records found.
                </td>
              </tr>

              <!-- Actual data -->
              <tr v-else-if="data && data.data" v-for="row in data.data" :key="row.id">
                <td>{{ row.date }}</td>
                <td>
                  <a :href="`/no-contract-invoice/${encodeURIComponent(row.refCode)}`" class="text-primary text-decoration-none hover-underline">
                    {{ row.refCode }}
                  </a>
                </td>
                <td>
                  <a :href="`/page/maid-finance/${encodeURIComponent(row.maid_relation.name)}`" class="text-primary text-decoration-none hover-underline">
                    {{ row.maid_relation.name }}
                  </a>
                </td>
                <td>
                  <a :href="`/get-maids-salary-p1-by-name/${encodeURIComponent(row.maid_relation.name)}`" class="text-primary text-decoration-none hover-underline">
                    Statement
                  </a>
                </td>
                <td>{{ row.amount }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination Footer -->
      <div v-if="isLoading || (data && total > 0)" class="card-footer salary-footer">
        <nav class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <!-- Pagination controls -->
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <a class="page-link" href="#" @click.prevent="currentPage > 1 && handlePageChange(currentPage - 1)">
                <i class="ki-duotone ki-left fs-5">
                  <i class="path1"></i>
                  <i class="path2"></i>
                </i>
                Previous
              </a>
            </li>

            <li v-if="visiblePages[0] > 1" class="page-item">
              <a class="page-link" href="#" @click.prevent="handlePageChange(1)">1</a>
            </li>

            <li v-if="visiblePages[0] > 2" class="page-item disabled">
              <span class="page-link">...</span>
            </li>

            <li v-for="pageNum in visiblePages" :key="pageNum" class="page-item" :class="{ active: pageNum === currentPage }">
              <a class="page-link" href="#" @click.prevent="handlePageChange(pageNum)">{{ pageNum }}</a>
            </li>

            <li v-if="visiblePages[visiblePages.length - 1] < totalPages - 1" class="page-item disabled">
              <span class="page-link">...</span>
            </li>

            <li v-if="visiblePages[visiblePages.length - 1] < totalPages" class="page-item">
              <a class="page-link" href="#" @click.prevent="handlePageChange(totalPages)">{{ totalPages }}</a>
            </li>

            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <a class="page-link" href="#" @click.prevent="currentPage < totalPages && handlePageChange(currentPage + 1)">
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
            Total Results: <strong class="text-primary">{{ total || 0 }}</strong>
          </div>
        </nav>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Theme-adaptive styling using Bootstrap CSS variables */
.salary-container {
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  margin: 1.5rem 3rem;
}

.salary-card {
  background-color: var(--bs-card-bg);
  border-color: var(--bs-border-color);
}

.salary-table {
  color: var(--bs-body-color);
}

.salary-table thead {
  background-color: var(--bs-body-bg);
}

.salary-table thead th {
  padding: 1rem;
  font-weight: 600;
  color: var(--bs-emphasis-color);
  white-space: nowrap;
  font-size: 0.85rem;
  border-bottom: 2px solid var(--bs-border-color);
}

.salary-table th:first-child,
.salary-table td:first-child {
  padding-left: 1.25rem;
}

.salary-table tbody td {
  padding: 0.75rem 1rem;
  vertical-align: middle;
  border-bottom: 1px solid var(--bs-border-color);
}

.salary-table tbody tr:hover {
  background-color: var(--bs-tertiary-bg);
}

.salary-footer {
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

/* Responsive adjustments */
@media (max-width: 768px) {
  .salary-container {
    margin: 1rem;
  }

  .salary-table thead th,
  .salary-table tbody td {
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
  height: 1.25rem;
}

.skeleton-text {
  height: 1.25rem;
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

.hover-underline:hover {
  text-decoration: underline !important;
}
</style>
