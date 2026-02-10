<template>
  <div class="subscription-container">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <h2 class="h4 fw-bold mb-0 d-flex align-items-center">
        <i class="ki-duotone ki-card fs-1 me-2 text-primary">
          <i class="path1"></i>
          <i class="path2"></i>
        </i>
        Subscriptions
      </h2>
    </div>

    <!-- Controls Toolbar -->
    <div class="card subscription-card mb-4 border-0 shadow-sm">
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
                placeholder="Search subscriptions..."
                @input="reset"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="card subscription-card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover subscription-table mb-0">
            <thead>
              <tr>
                <th>
                  <i class="ki-duotone ki-barcode fs-5 me-1 text-primary">
                    <i class="path1"></i>
                    <i class="path2"></i>
                    <i class="path3"></i>
                    <i class="path4"></i>
                    <i class="path5"></i>
                    <i class="path6"></i>
                    <i class="path7"></i>
                    <i class="path8"></i>
                  </i>
                  ID
                </th>
                <th>
                  <i class="ki-duotone ki-user fs-5 me-1 text-info">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Customer
                </th>
                <th>Status</th>
                <th>
                  <i class="ki-duotone ki-calendar fs-5 me-1 text-warning">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  Created
                </th>
                <th>
                  <i class="ki-duotone ki-profile-user fs-5 me-1 text-success">
                    <i class="path1"></i>
                    <i class="path2"></i>
                    <i class="path3"></i>
                    <i class="path4"></i>
                  </i>
                  Plan Customer
                </th>
              </tr>
            </thead>
            <tbody>
              <!-- Loading state -->
              <template v-if="loading">
                <tr v-for="i in 5" :key="`skeleton-${i}`" class="skeleton-row">
                  <td><div class="skeleton skeleton-text" style="width: 150px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 120px"></div></td>
                  <td><div class="skeleton skeleton-badge" style="width: 80px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 100px"></div></td>
                  <td><div class="skeleton skeleton-text" style="width: 120px"></div></td>
                </tr>
              </template>

              <!-- Empty state -->
              <tr v-else-if="!loading && subscriptions && !subscriptions.length">
                <td colspan="5" class="text-center text-muted py-4">
                  <i class="ki-duotone ki-inbox fs-1 d-block mb-2">
                    <i class="path1"></i>
                    <i class="path2"></i>
                  </i>
                  No subscriptions found.
                </td>
              </tr>

              <!-- Actual data -->
              <tr v-else-if="subscriptions" v-for="row in subscriptions" :key="row.id">
                <td>{{ row.id }}</td>
                <td>{{ row.customer }}</td>
                <td>
                  <span class="badge" :class="{
                    'bg-success': row.status === 'active',
                    'bg-warning text-dark': row.status === 'trialing',
                    'bg-danger': row.status === 'canceled' || row.status === 'unpaid',
                    'bg-secondary': !['active', 'trialing', 'canceled', 'unpaid'].includes(row.status)
                  }">
                    {{ row.status }}
                  </span>
                </td>
                <td>{{ new Date(row.created * 1000).toLocaleString() }}</td>
                <td>{{ row?.items?.data?.[0]?.plan?.metadata?.customer || 'N/A' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination Footer -->
      <div class="card-footer subscription-footer">
        <div class="d-flex justify-content-between align-items-center">
          <button 
            class="btn btn-sm btn-outline-primary d-flex align-items-center" 
            @click="navigate('prev')"
            :disabled="prevCursors.length === 0 || loading"
          >
            <i class="ki-duotone ki-left fs-5 me-1">
              <i class="path1"></i>
              <i class="path2"></i>
            </i>
            Previous
          </button>

          <button 
            class="btn btn-sm btn-outline-primary d-flex align-items-center" 
            @click="navigate('next')"
            :disabled="!hasMore || loading"
          >
            Next
            <i class="ki-duotone ki-right fs-5 ms-1">
              <i class="path1"></i>
              <i class="path2"></i>
            </i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useSubscriptions } from "/resources/js/hooks/useSubscriptions";

const searchQuery = ref('');
const loading = ref(false);

const {
  subscriptions,
  nextPage,
  previousPage,
  prevCursors,
  hasMore,
} = useSubscriptions();

// Placeholder for reset function if it was intended to be used
const reset = () => {
  // Implement reset logic if needed, e.g., clear filters or reload data
  // Currently just a placeholder as it was referenced in the original template
};

function navigate(direction: 'next' | 'prev') {
  loading.value = true;

  // Simulate async operation delay if needed, or just call the functions
  // In a real app, nextPage/previousPage might be async
  setTimeout(() => {
    if (direction === 'next') {
      nextPage();
    } else {
      previousPage();
    }
    loading.value = false;
  }, 500); 
}
</script>

<style scoped>
/* Theme-adaptive styling using Bootstrap CSS variables */
.subscription-container {
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  margin: 1.5rem 3rem;
}

.subscription-card {
  background-color: var(--bs-card-bg);
  border-color: var(--bs-border-color);
}

.subscription-table {
  color: var(--bs-body-color);
}

.subscription-table thead {
  background-color: var(--bs-body-bg);
}

.subscription-table thead th {
  padding: 1rem;
  font-weight: 600;
  color: var(--bs-emphasis-color);
  white-space: nowrap;
  font-size: 0.85rem;
  border-bottom: 2px solid var(--bs-border-color);
}

.subscription-table th:first-child,
.subscription-table td:first-child {
  padding-left: 1.25rem;
}

.subscription-table tbody td {
  padding: 0.75rem 1rem;
  vertical-align: middle;
  border-bottom: 1px solid var(--bs-border-color);
}

.subscription-table tbody tr:hover {
  background-color: var(--bs-tertiary-bg);
}

.subscription-footer {
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
  .subscription-container {
    margin: 1rem;
  }

  .subscription-table thead th,
  .subscription-table tbody td {
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

.skeleton-badge {
  height: 1.25rem;
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
