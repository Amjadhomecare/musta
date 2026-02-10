<template>
  <div class="balance-container">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <h2 class="h4 fw-bold mb-0 d-flex align-items-center">
        <i class="ki-duotone ki-wallet fs-1 me-2 text-primary">
          <i class="path1"></i>
          <i class="path2"></i>
          <i class="path3"></i>
          <i class="path4"></i>
        </i>
        Customer Balances
      </h2>
    </div>

    <!-- Controls Toolbar -->
    <div class="card balance-card mb-4 border-0 shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row align-items-md-end gap-3">
          <!-- Filter -->
          <div class="flex-grow-1" style="max-width: 300px;">
            <label class="form-label fw-semibold text-muted small text-uppercase mb-1">Balance Filter</label>
            <select v-model="balanceFilter" class="form-select" @change="loadData">
              <option value="">All (≠ 0)</option>
              <option value="positive">Positive Only</option>
              <option value="negative">Negative Only</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Data Content -->
    <div v-if="loading" class="card balance-card border-0 shadow-sm p-4">
      <div class="d-flex justify-content-center align-items-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    </div>

    <div v-else-if="data && Object.keys(data).length > 0">
      <div v-for="(entries, group) in data" :key="group" class="card balance-card border-0 shadow-sm mb-4">
        <div class="card-header bg-light py-3">
          <h3 class="h5 fw-bold mb-0 text-uppercase text-primary d-flex align-items-center">
            <i class="ki-duotone ki-folder fs-2 me-2">
              <i class="path1"></i>
              <i class="path2"></i>
            </i>
            {{ group }} Accounts
          </h3>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped table-hover balance-table mb-0">
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
                    Ledger ID
                  </th>
                  <th>
                    <i class="ki-duotone ki-profile-user fs-5 me-1 text-info">
                      <i class="path1"></i>
                      <i class="path2"></i>
                      <i class="path3"></i>
                      <i class="path4"></i>
                    </i>
                    Account Name
                  </th>
                  <th>
                    <i class="ki-duotone ki-dollar fs-5 me-1 text-success">
                      <i class="path1"></i>
                      <i class="path2"></i>
                      <i class="path3"></i>
                    </i>
                    Balance
                  </th>
                  <th>
                    <i class="ki-duotone ki-calendar fs-5 me-1 text-warning">
                      <i class="path1"></i>
                      <i class="path2"></i>
                    </i>
                    Latest Date
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in entries" :key="row.ledger_id">
                  <td>{{ row.ledger_id }}</td>
                  <td>
                    <a
                      :href="`/customer/soa/${row.account_name}`"
                      target="_blank"
                      class="text-primary text-decoration-none hover-underline fw-semibold"
                    >
                      {{ row.account_name }}
                    </a>
                  </td>
                  <td :class="{ 'text-danger fw-bold': parseFloat(row.balance) < 0, 'text-success fw-bold': parseFloat(row.balance) > 0 }">
                    {{ row.balance }}
                  </td>
                  <td>{{ row.latest_date }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="card balance-card border-0 shadow-sm">
      <div class="card-body text-center py-5">
        <i class="ki-duotone ki-inbox fs-5x text-muted mb-3">
          <i class="path1"></i>
          <i class="path2"></i>
        </i>
        <h4 class="fw-bold text-muted">No Data Found</h4>
        <p class="text-muted mb-0">Try adjusting your filters or check back later.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const data = ref({})
const balanceFilter = ref('')
const loading = ref(false)

const loadData = async () => {
  loading.value = true
  try {
    const params = balanceFilter.value ? `?balance=${balanceFilter.value}` : ''
    const res = await fetch(`/customer-balances${params}`, {
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    if (!res.ok) throw new Error('Failed to fetch')
    data.value = await res.json()
  } catch (err) {
    console.error('Error loading data:', err)
    data.value = {}
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
/* Theme-adaptive styling using Bootstrap CSS variables */
.balance-container {
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  margin: 1.5rem 3rem;
}

.balance-card {
  background-color: var(--bs-card-bg);
  border-color: var(--bs-border-color);
}

.balance-table {
  color: var(--bs-body-color);
}

.balance-table thead {
  background-color: var(--bs-body-bg);
}

.balance-table thead th {
  padding: 1rem;
  font-weight: 600;
  color: var(--bs-emphasis-color);
  white-space: nowrap;
  font-size: 0.85rem;
  border-bottom: 2px solid var(--bs-border-color);
}

.balance-table th:first-child,
.balance-table td:first-child {
  padding-left: 1.25rem;
}

.balance-table tbody td {
  padding: 0.75rem 1rem;
  vertical-align: middle;
  border-bottom: 1px solid var(--bs-border-color);
}

.balance-table tbody tr:hover {
  background-color: var(--bs-tertiary-bg);
}

/* Form controls */
.form-select {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
  color: var(--bs-body-color);
}

.form-select:focus {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-primary);
  color: var(--bs-body-color);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .balance-container {
    margin: 1rem;
  }

  .balance-table thead th,
  .balance-table tbody td {
    padding: 0.5rem;
    font-size: 0.85rem;
  }
}

.hover-underline:hover {
  text-decoration: underline !important;
}
</style>
