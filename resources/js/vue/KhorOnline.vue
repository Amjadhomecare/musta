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
  userPerformancePieChart: null,
  nationalityChart: null
})

// Fetch data function
const fetchContractReturnsData = async () => {
  const response = await axios.get('https://api.alahliamaids.com/KhorContractReturnStatus/users-contracts-returns', {
    params: {
      fromDate: startDate.value,
      toDate: endDate.value
    }
  })
  return response.data
}

// Vue Query
const { data, isLoading, isError, refetch } = useQuery({
  queryKey: ['contractReturnsData', startDate, endDate],
  queryFn: fetchContractReturnsData,
  refetchInterval: 300000, // Refetch every 5 minutes
})

// Computed property for available users
const availableUsers = computed(() => {
  if (!data.value) return []
  const users = data.value.users
    .map(user => user.userName)
    .sort()
  return users
})

// Computed properties for chart data
const summaryCardData = computed(() => {
  if (!data.value) return []
  return [
    { title: 'Total Online Users', value: data.value.summary.totalOnlineUsers, color: 'secondary', icon: '👥' },
    { title: 'Users with Contracts', value: data.value.summary.usersWithContracts, color: 'dark', icon: '📊' },
    { title: 'Total Contracts', value: data.value.summary.totalContracts, color: 'primary', icon: '📋' },
    { title: 'Active Contracts', value: data.value.summary.totalActiveContracts, color: 'success', icon: '✅' },
    { title: 'Total Working Days', value: data.value.summary.totalWorkingDays, color: 'info', icon: '📅' }
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
    userPerformancePieChart: null,
    nationalityChart: null
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
  const userData = data.value.users.find(user => user.userName === selectedUser.value)
  
  if (!userData) return

  const activeContracts = userData.activeContracts
  const returnedContracts = userData.totalContracts - userData.activeContracts

  charts.value.userPerformancePieChart = new ApexCharts(userChartEl, {
    series: [activeContracts, returnedContracts],
    chart: {
      type: 'pie',
      height: 350,
      background: 'transparent'
    },
    labels: ['Active Contracts', 'Returned Contracts'],
    colors: ['#10B981', '#EF4444'],
    dataLabels: {
      enabled: true,
      formatter: function (val, opts) {
        const count = opts.w.globals.series[opts.seriesIndex]
        return count + ' contracts'
      }
    },
    legend: {
      position: 'bottom',
      labels: { colors: textColor }
    },
    tooltip: {
      theme: isDarkMode ? 'dark' : 'light',
      y: { formatter: val => val + ' contracts' }
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
    if (userChartEl && data.value?.users) {
      const allUsers = data.value.users // Show ALL users, including those with 0 contracts
      
      const categories = allUsers.map(user => user.userName)
      const totalContractsData = allUsers.map(user => user.totalContracts)
      const activeContractsData = allUsers.map(user => user.activeContracts)
      const totalWorkingDaysData = allUsers.map(user => user.totalWorkingDays)

      charts.value.userChart = new ApexCharts(userChartEl, {
        series: [
          { name: 'Total Contracts', data: totalContractsData },
          { name: 'Active Contracts', data: activeContractsData },
          { name: 'Total Working Days', data: totalWorkingDaysData }
        ],
        chart: { 
          type: 'bar', 
          height: 350, 
          toolbar: { show: true },
          background: 'transparent'
        },
        plotOptions: {
          bar: { 
            horizontal: false, 
            columnWidth: '55%', 
            borderRadius: 5,
            dataLabels: {
              position: 'top'
            }
          }
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
        colors: ['#3B82F6', '#10B981', '#F59E0B'],
        grid: { borderColor: gridColor },
        tooltip: {
          theme: isDarkMode ? 'dark' : 'light',
          shared: true,
          intersect: false,
          custom: function({ series, seriesIndex, dataPointIndex, w }) {
            const userData = allUsers[dataPointIndex]
            return `
              <div class="apexcharts-tooltip-custom" style="padding: 10px; background: ${isDarkMode ? '#1e1e2e' : '#ffffff'}; border: 1px solid ${isDarkMode ? '#323248' : '#e4e6ef'}; border-radius: 6px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <div style="font-weight: bold; color: ${isDarkMode ? '#92929f' : '#5e6278'}; margin-bottom: 8px; font-size: 14px;">${userData.userName}</div>
                <div style="color: #3B82F6; margin: 4px 0; font-size: 13px;">
                  <span style="display: inline-block; width: 10px; height: 10px; background: #3B82F6; border-radius: 50%; margin-right: 6px;"></span>
                  Total Contracts: <strong>${userData.totalContracts}</strong>
                </div>
                <div style="color: #10B981; margin: 4px 0; font-size: 13px;">
                  <span style="display: inline-block; width: 10px; height: 10px; background: #10B981; border-radius: 50%; margin-right: 6px;"></span>
                  Active Contracts: <strong>${userData.activeContracts}</strong>
                </div>
                <div style="color: #F59E0B; margin: 4px 0; font-size: 13px;">
                  <span style="display: inline-block; width: 10px; height: 10px; background: #F59E0B; border-radius: 50%; margin-right: 6px;"></span>
                  Total Working Days: <strong>${userData.totalWorkingDays}</strong>
                </div>
                <div style="color: #EF4444; margin: 4px 0; font-size: 13px; border-top: 1px solid ${isDarkMode ? '#323248' : '#e4e6ef'}; padding-top: 4px;">
                  <span style="display: inline-block; width: 10px; height: 10px; background: #EF4444; border-radius: 50%; margin-right: 6px;"></span>
                  Returned Contracts: <strong>${userData.totalContracts - userData.activeContracts}</strong>
                </div>
              </div>
            `
          }
        },
        legend: { labels: { colors: textColor } }
      })
      charts.value.userChart.render()
    }

    // Nationality Chart
    const nationalityChartEl = document.querySelector("#nationalityChart")
    if (nationalityChartEl && data.value?.nationalityStats) {
      const nationalityData = data.value.nationalityStats
      
      const nationalityCategories = nationalityData.map(item => item.nationality)
      const nationalityTotalContractsData = nationalityData.map(item => item.totalContracts)
      const nationalityActiveContractsData = nationalityData.map(item => item.activeContracts)
      const nationalityTotalWorkingDaysData = nationalityData.map(item => item.totalWorkingDays)

      charts.value.nationalityChart = new ApexCharts(nationalityChartEl, {
        series: [
          { name: 'Total Contracts', data: nationalityTotalContractsData },
          { name: 'Active Contracts', data: nationalityActiveContractsData },
          { name: 'Total Working Days', data: nationalityTotalWorkingDaysData }
        ],
        chart: { 
          type: 'bar', 
          height: 350, 
          toolbar: { show: true },
          background: 'transparent'
        },
        plotOptions: {
          bar: { 
            horizontal: false, 
            columnWidth: '55%', 
            borderRadius: 5,
            dataLabels: {
              position: 'top'
            }
          }
        },
        dataLabels: { enabled: false },
        stroke: { show: true, width: 2, colors: ['transparent'] },
        xaxis: { 
          categories: nationalityCategories,
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
        colors: ['#3B82F6', '#10B981', '#F59E0B'],
        grid: { borderColor: gridColor },
        tooltip: {
          theme: isDarkMode ? 'dark' : 'light',
          shared: true,
          intersect: false,
          custom: function({ series, seriesIndex, dataPointIndex, w }) {
            const nationalityInfo = nationalityData[dataPointIndex]
            return `
              <div class="apexcharts-tooltip-custom" style="padding: 10px; background: ${isDarkMode ? '#1e1e2e' : '#ffffff'}; border: 1px solid ${isDarkMode ? '#323248' : '#e4e6ef'}; border-radius: 6px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <div style="font-weight: bold; color: ${isDarkMode ? '#92929f' : '#5e6278'}; margin-bottom: 8px; font-size: 14px;">${nationalityInfo.nationality}</div>
                <div style="color: #3B82F6; margin: 4px 0; font-size: 13px;">
                  <span style="display: inline-block; width: 10px; height: 10px; background: #3B82F6; border-radius: 50%; margin-right: 6px;"></span>
                  Total Contracts: <strong>${nationalityInfo.totalContracts}</strong>
                </div>
                <div style="color: #10B981; margin: 4px 0; font-size: 13px;">
                  <span style="display: inline-block; width: 10px; height: 10px; background: #10B981; border-radius: 50%; margin-right: 6px;"></span>
                  Active Contracts: <strong>${nationalityInfo.activeContracts}</strong>
                </div>
                <div style="color: #F59E0B; margin: 4px 0; font-size: 13px;">
                  <span style="display: inline-block; width: 10px; height: 10px; background: #F59E0B; border-radius: 50%; margin-right: 6px;"></span>
                  Total Working Days: <strong>${nationalityInfo.totalWorkingDays}</strong>
                </div>
                <div style="color: #EF4444; margin: 4px 0; font-size: 13px; border-top: 1px solid ${isDarkMode ? '#323248' : '#e4e6ef'}; padding-top: 4px;">
                  <span style="display: inline-block; width: 10px; height: 10px; background: #EF4444; border-radius: 50%; margin-right: 6px;"></span>
                  Returned Contracts: <strong>${nationalityInfo.totalContracts - nationalityInfo.activeContracts}</strong>
                </div>
              </div>
            `
          }
        },
        legend: { labels: { colors: textColor } }
      })
      charts.value.nationalityChart.render()
    }

    // Initialize user performance pie chart if user is selected
    if (selectedUser.value) {
      updateUserPerformancePieChart()
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
  <div class="container-fluid pb-10 px-10 pt-10">
    <!-- Header -->
    <div class="row mb-5">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-lg-6 mb-3 mb-lg-0">
                <h1 class="fs-2x fw-bold text-gray-900 mb-0">📊 Online Report Homecare & Family Care</h1>
                <p class="text-gray-600 fs-5 mb-0">User performance and contract analytics</p>
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
      <p class="text-gray-600 mt-3">Loading contract returns data...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="isError" class="alert alert-danger d-flex align-items-center" role="alert">
      <i class="ki-duotone ki-information-5 fs-2x me-3">
        <span class="path1"></span>
        <span class="path2"></span>
        <span class="path3"></span>
      </i>
      <div>
        Error loading contract returns data. Please try again.
      </div>
    </div>

    <!-- Dashboard Content -->
    <div v-else-if="data">
      <!-- Summary Cards -->
      <div class="row g-5 mb-5">
        <div v-for="card in summaryCardData" :key="card.title" class="col-sm-6 col-xl">
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
        <!-- Individual User Performance -->
        <div class="col-xl-3">
          <div class="card h-100">
            <div class="card-header border-0 pt-5">
              <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">🎯 Individual User Performance</span>
                <span class="text-muted fw-semibold fs-7">Contract status for selected user</span>
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
        
        <!-- User Contributions -->
        <div class="col-xl-9">
          <div class="card h-100">
            <div class="card-header border-0 pt-5">
              <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">👥 User Contributions by Contract Status</span>
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

      <!-- Nationality Chart Row -->
      <div class="row g-5 mb-5">
        <div class="col-12">
          <div class="card h-100">
            <div class="card-header border-0 pt-5">
              <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">🌍 Nationality Performance Analytics</span>
                <span class="text-muted fw-semibold fs-7">Contract distribution and performance by nationality</span>
              </h3>
              <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary">
                  <i class="ki-duotone ki-geolocation fs-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                  </i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <div id="nationalityChart"></div>
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
</style>