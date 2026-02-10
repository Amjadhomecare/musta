<template>
  <div class="report-container">
    <div class="card report-card shadow-sm">
      <!-- Header -->
      <div class="card-header bg-primary text-white text-center py-3">
        <h2 class="h3 fw-bold mb-0">NEXT META REPORT</h2>
      </div>

      <div class="card-body p-4">
        <!-- Filters -->
        <form class="row g-3 align-items-end mb-4" @submit.prevent="fetchReport">
          <div class="col-md-3">
            <label class="form-label fw-semibold">Start Date</label>
            <input
              v-model="state.startDate"
              type="date"
              class="form-control"
              placeholder="Select Start Date"
            />
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">End Date</label>
            <input
              v-model="state.endDate"
              type="date"
              class="form-control"
              placeholder="Select End Date"
            />
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              Generate Report
            </button>
          </div>
        </form>

        <!-- Error Message -->
        <div v-if="error" class="alert alert-danger d-flex align-items-center mb-4" role="alert">
          <i class="ki-duotone ki-cross-circle fs-2 me-2">
            <i class="path1"></i>
            <i class="path2"></i>
          </i>
          <div>{{ error }}</div>
        </div>

        <!-- Report Content -->
        <div v-if="!loading && state.reportData">
          <!-- Summary Statistics -->
          <section class="mb-5">
            <h3 class="h5 fw-bold mb-3 text-muted text-uppercase">Summary</h3>
            <div class="row g-3">
              <div
                class="col-6 col-md-3"
                v-for="(value, label) in summaryCounts"
                :key="label"
              >
                <div class="card h-100 border-0 shadow-sm bg-light summary-card">
                  <div class="card-body text-center p-3">
                    <div class="display-6 fw-bold text-primary mb-1">{{ value }}</div>
                    <div class="small text-muted fw-semibold">{{ label }}</div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <hr class="my-5 opacity-10" />

          <!-- Data Tables -->
          <div class="row g-4">
            <div
              v-for="(section, idx) in tableSections"
              :key="idx"
              class="col-md-6"
            >
              <div class="card h-100 border shadow-sm">
                <div class="card-header bg-light py-2">
                  <h4 class="h6 fw-bold mb-0 text-uppercase">{{ section.title }}</h4>
                </div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 report-table">
                      <thead>
                        <tr>
                          <th
                            v-for="(col, index) in section.columns"
                            :key="index"
                            class="text-center"
                          >
                            {{ col.label }}
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="!section.data || !section.data.length">
                          <td :colspan="section.columns.length" class="text-center text-muted py-3">
                            No data available
                          </td>
                        </tr>
                        <tr v-for="(row, rIndex) in section.data" :key="rIndex">
                          <td
                            v-for="(col, cIndex) in section.columns"
                            :key="cIndex"
                            class="text-center"
                          >
                            {{ row[col.prop] }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useReport } from '/resources/js/hooks/useReport'

const { state, loading, error, fetchReport } = useReport()

const summaryCounts = computed(() => ({
  'Return P1': state.reportData?.maidReturnCat1_count || 0,
  'Returned P4': state.reportData?.returnedMaid_count || 0,
  'Release & Ranaway': state.reportData?.release_count || 0,
  'Arrival Count': state.reportData?.arrival_count || 0,
  'P1 Count': state.reportData?.p1Count || 0,
  'P4 Count': state.reportData?.p4Count || 0,
  'Typing Count': state.reportData?.typing_count || 0
}))

const tableSections = computed(() => [
  {
    title: 'P1 By Sales',
    data: state.reportData?.categoryOne_counts || [],
    columns: [
      { prop: 'created_by', label: 'Employee' },
      { prop: 'total', label: 'Total' }
    ]
  },
  {
    title: 'P4 By Sales',
    data: state.reportData?.category4Model_counts || [],
    columns: [
      { prop: 'created_by', label: 'Employee' },
      { prop: 'total', label: 'Total' }
    ]
  },
  {
    title: 'Released and Ranaway',
    data: state.reportData?.relase || [],
    columns: [
      { prop: 'new_status', label: 'Status' },
      { prop: 'total', label: 'Total' }
    ]
  },
  {
    title: 'Cash Report',
    data: state.reportData?.cash || [],
    columns: [
      { prop: 'ledger', label: 'Cash & Equivalents' },
      { prop: 'total_received', label: 'Total' }
    ]
  }
])
</script>

<style scoped>
/* Theme-adaptive styling using Bootstrap CSS variables */
.report-container {
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  margin: 1.5rem 3rem;
}

.report-card {
  background-color: var(--bs-card-bg);
  border-color: var(--bs-border-color);
}

.report-table {
  color: var(--bs-body-color);
}

.report-table thead {
  background-color: var(--bs-body-bg);
}

.report-table thead th {
  padding: 0.75rem;
  font-weight: 600;
  color: var(--bs-emphasis-color);
  font-size: 0.85rem;
  border-bottom: 2px solid var(--bs-border-color);
}

.report-table tbody td {
  padding: 0.5rem 0.75rem;
  vertical-align: middle;
  border-bottom: 1px solid var(--bs-border-color);
}

.report-table tbody tr:hover {
  background-color: var(--bs-tertiary-bg);
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

/* Summary Cards */
.summary-card {
  background-color: var(--bs-tertiary-bg) !important;
  transition: transform 0.2s;
}

.summary-card:hover {
  transform: translateY(-2px);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .report-container {
    margin: 1rem;
  }
}
</style>
