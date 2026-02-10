<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import axios from 'axios'

// Date state
const startDate = ref(new Date().toISOString().split('T')[0])
const endDate = ref(new Date().toISOString().split('T')[0])

// Selected user for pie chart
const selectedUser = ref('')

// Chart instances
const charts = ref({
  userChart: null,
  cashFlowChart: null,
  balanceChart: null,
  userPerformancePieChart: null
})

// Fetch data function
const fetchDashboardData = async () => {
  const response = await axios.post('/api/onclick', {
    start_date: startDate.value,
    end_date: endDate.value
  })
  return response.data
}

// Vue Query
const { data, isLoading, isError, refetch } = useQuery({
  queryKey: ['dashboardData', startDate, endDate],
  queryFn: fetchDashboardData,
  refetchInterval: 300000, // Refetch every 5 minutes
})

// Format currency
const formatCurrency = (amount) => {
  const num = parseFloat(amount)
  return new Intl.NumberFormat('en-AE', {
    style: 'currency',
    currency: 'AED',
    minimumFractionDigits: 2
  }).format(Math.abs(num))
}

// Computed property for available users
const availableUsers = computed(() => {
  if (!data.value) return []
  const users = new Set([
    ...data.value.categoryOne_counts.map(item => item.created_by),
    ...data.value.category4Model_counts.map(item => item.created_by)
  ])
  return Array.from(users).sort()
})

// Computed properties for chart data
const summaryCardData = computed(() => {
  if (!data.value) return []
  return [
    { title: 'Package 1 Total', value: data.value.p1Count, color: 'secondary', icon: '📊' },
    { title: 'Package 4 Total', value: data.value.p4Count, color: 'dark', icon: '📊' },
    { title: 'Package 1 Returns', value: data.value.maidReturnCat1_count, color: 'primary', icon: '📦' },
    { title: 'Package 4 Returns', value: data.value.returnedMaid_count, color: 'info', icon: '📦' },
    { title: 'Typing Invoices', value: data.value.typing_count, color: 'danger', icon: '📄' },
    { title: 'Releases', value: data.value.release_count, color: 'success', icon: '✅' },
    { title: 'Arrivals', value: data.value.arrival_count, color: 'warning', icon: '✈️' }
  ]
})

// Destroy existing charts
const destroyCharts = () => {
  Object.values(charts.value).forEach(chart => {
    if (chart) {
      chart.destroy()
    }
  })
  charts.value = {
    userChart: null,
    cashFlowChart: null,
    balanceChart: null,
    userPerformancePieChart: null
  }
}

// Update user performance pie chart
const updateUserPerformancePieChart = () => {
  if (!selectedUser.value || !data.value) return

  const userChartEl = document.querySelector("#userPerformancePieChart")
  if (!userChartEl) return

  // Destroy existing chart
  if (charts.value.userPerformancePieChart) {
    charts.value.userPerformancePieChart.destroy()
  }

  const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark'
  const textColor = isDarkMode ? '#92929f' : '#5E6278'

  // Get data for selected user
  const p1Data = data.value.categoryOne_counts.find(item => item.created_by === selectedUser.value)
  const p4Data = data.value.category4Model_counts.find(item => item.created_by === selectedUser.value)
  
  const p1Count = p1Data ? p1Data.total : 0
  const p4Count = p4Data ? p4Data.total : 0

  charts.value.userPerformancePieChart = new ApexCharts(userChartEl, {
    series: [p1Count, p4Count],
    chart: {
      type: 'pie',
      height: 350,
      background: 'transparent'
    },
    labels: ['Package 1', 'Package 4'],
    colors: ['#3B82F6', '#10B981'],
    dataLabels: {
      enabled: true,
      formatter: function (val, opts) {
        const count = opts.w.globals.series[opts.seriesIndex]
        return count + ' items'
      }
    },
    legend: {
      position: 'bottom',
      labels: { colors: textColor }
    },
    tooltip: {
      theme: isDarkMode ? 'dark' : 'light',
      y: { formatter: val => val + ' items' }
    },
    responsive: [{
      breakpoint: 480,
      options: {
        chart: {
          width: 300
        },
        legend: {
          position: 'bottom'
        }
      }
    }]
  })
  charts.value.userPerformancePieChart.render()
}

// Watch for selected user changes
watch(selectedUser, () => {
  updateUserPerformancePieChart()
})

// Initialize charts
const initializeCharts = async () => {
  if (!data.value) return
  
  // Wait for DOM to be ready
  await nextTick()
  
  // Wait a bit more for ApexCharts to load
  setTimeout(() => {
    if (typeof ApexCharts === 'undefined') {
      console.error('ApexCharts not loaded')
      return
    }

    // Destroy existing charts first
    destroyCharts()

    // Get theme mode
    const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark'
    const textColor = isDarkMode ? '#92929f' : '#5E6278'
    const gridColor = isDarkMode ? '#323248' : '#E4E6EF'

    // User Contribution Chart
    const userChartEl = document.querySelector("#userContributionChart")
    if (userChartEl && data.value?.categoryOne_counts && data.value?.category4Model_counts) {
      const allUsers = new Set([
        ...data.value.categoryOne_counts.map(item => item.created_by),
        ...data.value.category4Model_counts.map(item => item.created_by)
      ])
      
      const categories = Array.from(allUsers)
      const p1Data = categories.map(user => {
        const found = data.value.categoryOne_counts.find(item => item.created_by === user)
        return found ? found.total : 0
      })
      const p4Data = categories.map(user => {
        const found = data.value.category4Model_counts.find(item => item.created_by === user)
        return found ? found.total : 0
      })

      charts.value.userChart = new ApexCharts(userChartEl, {
        series: [
          { name: 'Package 1', data: p1Data },
          { name: 'Package 4', data: p4Data }
        ],
        chart: { 
          type: 'bar', 
          height: 350, 
          toolbar: { show: true },
          background: 'transparent'
        },
        plotOptions: {
          bar: { horizontal: false, columnWidth: '55%', borderRadius: 5 }
        },
        dataLabels: { enabled: false },
        stroke: { show: true, width: 2, colors: ['transparent'] },
        xaxis: { 
          categories: categories,
          labels: { style: { colors: textColor } }
        },
        yaxis: { 
          title: { 
            text: 'Count',
            style: { color: textColor }
          },
          labels: { style: { colors: textColor } }
        },
        fill: { opacity: 1 },
        colors: ['#3B82F6', '#10B981'],
        grid: { borderColor: gridColor },
        tooltip: {
          theme: isDarkMode ? 'dark' : 'light',
          y: { formatter: val => val + ' items' }
        },
        legend: { labels: { colors: textColor } }
      })
      charts.value.userChart.render()
    }

    // Initialize user performance pie chart if user is selected
    if (selectedUser.value) {
      updateUserPerformancePieChart()
    }

    // Cash Flow Chart
    const cashFlowEl = document.querySelector("#cashFlowChart")
    if (cashFlowEl && data.value?.cash && data.value?.cash_out) {
      const cashInTotal = data.value.cash.reduce((sum, item) => sum + parseFloat(item.total_received), 0)
      const cashOutTotal = data.value.cash_out.reduce((sum, item) => sum + parseFloat(item.total_paid), 0)

      charts.value.cashFlowChart = new ApexCharts(cashFlowEl, {
        series: [cashInTotal, cashOutTotal],
        chart: { 
          type: 'donut', 
          height: 350,
          background: 'transparent'
        },
        labels: ['Cash In', 'Cash Out'],
        colors: ['#10B981', '#EF4444'],
        plotOptions: {
          pie: {
            donut: {
              size: '70%',
              labels: {
                show: true,
                total: {
                  show: true,
                  label: 'Net Cash Flow',
                  color: textColor,
                  formatter: function (w) {
                    const diff = cashInTotal - cashOutTotal
                    return formatCurrency(diff)
                  }
                }
              }
            }
          }
        },
        dataLabels: { enabled: true },
        legend: { 
          position: 'bottom',
          labels: { colors: textColor }
        },
        tooltip: {
          theme: isDarkMode ? 'dark' : 'light',
          y: { formatter: val => formatCurrency(val) }
        }
      })
      charts.value.cashFlowChart.render()
    }

    const bsTextColor = (() => {
      const el = document.createElement('span')
      el.className = 'text-grey-900'
      el.style.display = 'none'
      document.body.appendChild(el)
      const c = getComputedStyle(el).color || '#212529' // fallback to Bootstrap body color
      el.remove()
      return c
    })()

    // Balance Chart
    const balanceEl = document.querySelector("#balanceChart")
    if (balanceEl && data.value?.closing_balance) {
      const sortedBalances = [...data.value.closing_balance].sort((a, b) => b.closing_balance - a.closing_balance)
      const top5 = sortedBalances.slice(0, 5)
      const bottom5 = sortedBalances.slice(-5).reverse()
      const selectedBalances = [...top5, ...bottom5]

      // INIT (keep your code; only added foreColor + dataLabels.style)
charts.value.balanceChart = new ApexCharts(balanceEl, {
  series: [{
    data: selectedBalances.map(item => ({
      x: item.ledger.length > 30 ? item.ledger.substring(0, 30) + '...' : item.ledger,
      y: parseFloat(item.closing_balance).toFixed(2)
    }))
  }],
  chart: { 
    type: 'bar', 
    height: 400, 
    toolbar: { show: true },
    background: 'transparent',
    foreColor: isDarkMode ? '#fff' : '#000'
  },
  plotOptions: {
    bar: {
      horizontal: true,
      borderRadius: 5,
      colors: {
        ranges: [{
          from: -Infinity,
          to: 0,
          color: '#EF4444'
        }, {
          from: 0,
          to: Infinity,
          color: '#10B981'
        }]
      }
    }
  },
  dataLabels: {
    enabled: true,
    style: { colors: [isDarkMode ? '#fff' : '#000'] },
    formatter: val => formatCurrency(val)
  },
  xaxis: {
    labels: {
      style: { colors: isDarkMode ? '#fff' : '#000' },
      formatter: val => formatCurrency(val)
    }
  },
  yaxis: {
    labels: { style: { colors: isDarkMode ? '#fff' : '#000' } }
  },
  grid: { borderColor: gridColor },
  tooltip: {
    theme: isDarkMode ? 'dark' : 'light',
    y: { formatter: val => formatCurrency(val) }
  }
})

      charts.value.balanceChart.render()

      const updateBalanceChartTheme = () => {
  const dark = document.documentElement.getAttribute('data-bs-theme') === 'dark'
            || document.documentElement.getAttribute('data-theme') === 'dark'
  const col = dark ? '#fff' : '#000'
  charts.value.balanceChart?.updateOptions({
    chart: { foreColor: col },
    dataLabels: { style: { colors: [col] } },
    xaxis: { labels: { style: { colors: col } } },
    yaxis: { labels: { style: { colors: col } } },
    tooltip: { theme: dark ? 'dark' : 'light' }
  }, false, true)
}

// run once and on future theme changes
updateBalanceChartTheme()
const _themeObserver = new MutationObserver(updateBalanceChartTheme)
_themeObserver.observe(document.documentElement, {
  attributes: true,
  attributeFilter: ['data-bs-theme', 'data-theme']
})
// optional (Metronic): also listen to their custom event if present
document.addEventListener('kt.thememode.change', updateBalanceChartTheme)
    }
  }, 500)
}

// Watch for data changes
watch(data, (newData) => {
  if (newData) {
    initializeCharts()
  }
}, { immediate: true })

// Handle date change
const handleDateChange = () => {
  refetch()
}
</script>

<template>
  <div class="container-fluid pb-10 px-10">
    <!-- Header -->
    <div class="row mb-5">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-lg-6 mb-3 mb-lg-0">
                <h1 class="fs-2x fw-bold text-gray-900 mb-0">📊 Dashboard</h1>
                <p class="text-gray-600 fs-5 mb-0">Real-time operational insights and analytics</p>
              </div>
              <div class="col-lg-6">
                <div class="row g-2">
                  <div class="col-md-5">
                    <label class="form-label text-gray-700 fw-semibold">Start Date</label>
                    <input 
                      type="date" 
                      v-model="startDate" 
                      @change="handleDateChange"
                      class="form-control form-control-solid"
                    >
                  </div>
                  <div class="col-md-5">
                    <label class="form-label text-gray-700 fw-semibold">End Date</label>
                    <input 
                      type="date" 
                      v-model="endDate" 
                      @change="handleDateChange"
                      class="form-control form-control-solid"
                    >
                  </div>
                  <div class="col-md-2 d-flex align-items-end">
                    <button @click="refetch" class="btn btn-primary w-100">
                      <i class="ki-duotone ki-arrows-circle fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                      </i>
                      Refresh
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-10">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="text-gray-600 mt-3">Loading dashboard data...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="isError" class="alert alert-danger d-flex align-items-center" role="alert">
      <i class="ki-duotone ki-information-5 fs-2x me-3">
        <span class="path1"></span>
        <span class="path2"></span>
        <span class="path3"></span>
      </i>
      <div>
        Error loading dashboard data. Please try again.
      </div>
    </div>

    <!-- Dashboard Content -->
    <div v-else-if="data">
      <!-- Summary Cards -->
      <div class="row g-5 mb-5">
        <div v-for="card in summaryCardData" :key="card.title" class="col-sm-6 col-xl-3">
          <div class="card hoverable h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <div class="fs-7 text-gray-600 fw-semibold">{{ card.title }}</div>
                <div class="fs-2x fw-bold text-gray-900 mt-1">{{ card.value }}</div>
              </div>
              <div class="symbol symbol-50px">
                <span :class="`symbol-label bg-light-${card.color} fs-2x`">
                  {{ card.icon }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="row g-5 mb-5">
        <!-- User Contributions -->

        <div class="col-xl-3">
          <div class="card h-100">
            <div class="card-header border-0 pt-5">
              <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">🎯 Individual User Performance</span>
                <span class="text-muted fw-semibold fs-7">Package distribution for selected user</span>
              </h3>
              <div class="card-toolbar">
                <select 
                  v-model="selectedUser" 
                  class="form-select form-select-solid form-select-sm w-200px"
                >
                  <option value="">Select a user...</option>
                  <option v-for="user in availableUsers" :key="user" :value="user">
                    {{ user }}
                  </option>
                </select>
              </div>
            </div>
            <div class="card-body">
              <div v-if="!selectedUser" class="text-center py-10">
                <i class="ki-duotone ki-user-square fs-3x text-gray-400 mb-3">
                  <span class="path1"></span>
                  <span class="path2"></span>
                  <span class="path3"></span>
                </i>
                <p class="text-gray-600">Please select a user to view their performance</p>
              </div>
              <div v-else id="userPerformancePieChart"></div>
            </div>
          </div>
        </div>
        
        <div class="col-xl-9">
          <div class="card h-100">
            <div class="card-header border-0 pt-5">
              <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">👥 User Contributions by Package</span>
                <span class="text-muted fw-semibold fs-7">Performance by team member</span>
              </h3>
              <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary">
                  <i class="ki-duotone ki-category fs-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                  </i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <div id="userContributionChart"></div>
            </div>
          </div>
        </div>

      </div>

      

      <!-- Balance Chart -->
      <div class="row g-5 mb-5">
        <div class="col-xl-8">
          <div class="card">
            <div class="card-header border-0 pt-5">
              <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">📊 Account Balances (Top & Bottom 5)</span>
                <span class="text-muted fw-semibold fs-7">Financial position by account</span>
              </h3>
            </div>
            <div class="card-body">
              <div id="balanceChart"></div>
            </div>
          </div>
        </div>
        <div class="col-xl-4">
          <div class="card h-100">
            <div class="card-header border-0 pt-5">
              <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">💰 Cash Flow Overview</span>
                <span class="text-muted fw-semibold fs-7">Income vs Expenses</span>
              </h3>
              <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary">
                  <i class="ki-duotone ki-finance-calculator fs-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                    <span class="path5"></span>
                    <span class="path6"></span>
                  </i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <div id="cashFlowChart"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tables Row -->
      <div class="row g-5 mb-5">
        <!-- Cash In Table -->
        <div class="col-xl-6">
          <div class="card h-100">
            <div class="card-header border-0 bg-light-success">
              <h3 class="card-title fw-bold text-success">📥 Cash Receipts</h3>
              <div class="card-toolbar">
                <span class="badge badge-success">{{ data.cash.length }} entries</span>
              </div>
            </div>
            <div class="card-body pt-0">
              <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                  <thead>
                    <tr class="fw-bold text-muted">
                      <th class="min-w-200px">Ledger</th>
                      <th class="min-w-100px text-end">Amount Received</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in data.cash" :key="item.ledger">
                      <td class="text-gray-900 fw-semibold">{{ item.ledger }}</td>
                      <td class="text-end">
                        <span class="badge badge-light-success fw-bold fs-7 p-2">
                          {{ formatCurrency(item.total_received) }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="fw-bold bg-light">
                      <td class="text-gray-900 ps-4">Total</td>
                      <td class="text-end text-success fs-6">
                        {{ formatCurrency(data.cash.reduce((sum, item) => sum + parseFloat(item.total_received), 0)) }}
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Cash Out Table -->
        <div class="col-xl-6">
          <div class="card h-100">
            <div class="card-header border-0 bg-light-danger">
              <h3 class="card-title fw-bold text-danger">📤 Cash Payments</h3>
              <div class="card-toolbar">
                <span class="badge badge-danger">{{ data.cash_out.length }} entries</span>
              </div>
            </div>
            <div class="card-body pt-0">
              <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                  <thead>
                    <tr class="fw-bold text-muted">
                      <th class="min-w-200px">Ledger</th>
                      <th class="min-w-100px text-end">Amount Paid</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in data.cash_out" :key="item.ledger">
                      <td class="text-gray-900 fw-semibold">{{ item.ledger }}</td>
                      <td class="text-end">
                        <span class="badge badge-light-danger fw-bold fs-7 p-2">
                          {{ formatCurrency(item.total_paid) }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="fw-bold bg-light">
                      <td class="text-gray-900 ps-4">Total</td>
                      <td class="text-end text-danger fs-6">
                        {{ formatCurrency(data.cash_out.reduce((sum, item) => sum + parseFloat(item.total_paid), 0)) }}
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Closing Balance Table -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header border-0 pt-5 bg-light-light pb-4">
              <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">💼 Closing Balances</span>
                <span class="text-muted fw-semibold fs-7">Current financial position by account</span>
              </h3>
            </div>
            <div class="card-body py-3">
              <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">
                  <thead>
                    <tr class="fw-bold text-muted">
                      <th class="min-w-200px">Ledger Account</th>
                      <th class="min-w-150px text-end">Closing Balance</th>
                      <th class="min-w-100px text-center">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in data.closing_balance" :key="item.ledger">
                      <td>
                        <span class="text-gray-900 fw-bold text-hover-primary fs-6">{{ item.ledger }}</span>
                      </td>
                      <td class="text-end">
                        <span class="fw-bold" :class="parseFloat(item.closing_balance) < 0 ? 'text-danger' : 'text-success'">
                          <span v-if="parseFloat(item.closing_balance) < 0">({{ formatCurrency(item.closing_balance) }})</span>
                          <span v-else>{{ formatCurrency(item.closing_balance) }}</span>
                        </span>
                      </td>
                      <td class="text-center">
                        <span class="badge" :class="parseFloat(item.closing_balance) < 0 ? 'badge-light-danger' : 'badge-light-success'">
                          {{ parseFloat(item.closing_balance) < 0 ? 'Deficit' : 'Surplus' }}
                        </span>
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
</template>

<style scoped>
/* Only minimal custom styles for Bootstrap overrides */
.hoverable {
  transition: transform 0.2s, box-shadow 0.2s;
}

.hoverable:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.table-responsive {
  max-height: 450px;
  overflow-y: auto;
}
</style>