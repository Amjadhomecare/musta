<template>
  <div class="container py-5">
    <!-- Filter Controls -->
    <div class="row gy-3 mb-4">
      <div class="col-md-4">
        <input
          v-model="searchQuery"
          @input="refetch"
          type="text"
          class="form-control"
          placeholder="🔍 Search name or nationality"
        />
      </div>

      <div class="col-md-4">
        <select
          v-model="nationalityFilter"
          @change="refetch"
          class="form-select"
        >
          <option value="">🌍 All Nationalities</option>
          <option value="Indonesia">🇮🇩 Indonesia</option>
          <option value="Ethiopia">🇪🇹 Ethiopia</option>
          <option value="Philippines">🇵🇭 Philippines</option>
          <option value="Myanmar">🇲🇲 Myanmar</option>
          <option value="Kenya">🇰🇪 Kenya</option>
          <option value="Uganda">🇺🇬 Uganda</option>
          <option value="Sri_Lanka">🇱🇰 Sri Lanka</option>
          <option value="Tanzanian">🇹🇿 Tanzania</option>
          <option value="India">🇮🇳 India</option>
          <option value="Ghana">🇬🇭 Ghana</option>
          <option value="nepal">🇳🇵 Nepal</option>
          <option value="pakistan">🇵🇰 Pakistan</option>
          <option value="zimbabwe">🇿🇼 Zimbabwe</option>
        </select>
      </div>

      <div class="col-md-4">
        <select
          v-model="pageSize"
          @change="handleSizeChange(pageSize)"
          class="form-select"
        >
          <option value="10">Show 10</option>
          <option value="100">Show 100</option>
          <option value="200">Show 200</option>
        </select>
      </div>
    </div>

    <!-- Total Count -->
    <div v-if="!isLoading" class="text-center text-muted mb-4">
      Total maids: <strong>{{ total }}</strong>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center text-secondary py-4">
      Loading data...
    </div>

    <!-- Cards Grid -->
    <div v-if="data?.data?.length" class="row g-4">
      <div v-for="maid in data.data" :key="maid.id" class="col-sm-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title">
              <a
                :href="`/maid-report/p4/${encodeURIComponent(maid.name)}`"
                class="link-primary text-decoration-none"
                target="_blank"
              >
                {{ maid.name }}
              </a>
            </h5>
            <p class="card-text small mb-1">
              🌐 <strong>Nationality:</strong> {{ maid.nationality }}
            </p>
            <p class="card-text small mb-1">
              ❌ <strong>Rejected by Maid:</strong> {{ maid.maid_rejected_count }}
            </p>
            <p class="card-text small mb-1">
              ❌ <strong>Rejected by Customer:</strong> {{ maid.customer_rejected_count }}
            </p>
            <p class="card-text small mb-1">
              ✅ <strong>Success:</strong> {{ maid.success_count }}
            </p>
            <p class="card-text small">
              ⏳ <strong>Pending:</strong> {{ maid.pending_count }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!isLoading" class="text-center text-muted py-4">
      😔 No maids found.
    </div>

    <!-- Pagination -->
    <div v-if="total > pageSize" class="d-flex justify-content-center mt-4">
      <nav class="d-flex align-items-center gap-3">
        <button
          class="btn btn-outline-primary rounded-pill"
          :disabled="currentPage === 1"
          @click="changePage(currentPage - 1)"
        >
          ⬅ Prev
        </button>

        <span class="text-secondary">
          Page <strong>{{ currentPage }}</strong> of {{ totalPages }}
        </span>

        <button
          class="btn btn-outline-primary rounded-pill"
          :disabled="currentPage >= totalPages"
          @click="changePage(currentPage + 1)"
        >
          Next ➡
        </button>
      </nav>
    </div>

    <!-- Export Button -->
    <div class="text-center mt-4">
      <button class="btn btn-success" @click="exportToExcel">
        📤 Export to Excel
      </button>
    </div>
  </div>
</template>


<script setup>
import { ref, computed } from 'vue'
import { usePaginationQuery } from '@/composables/usePagination'
import RemoteSelect from '../components/RemoteSelect.vue' 

// Filters
const filters = {
  nationality: ref('')
}

const nationalityFilter = computed({
  get: () => filters.nationality.value,
  set: (val) => (filters.nationality.value = val)
})

const {
  data,
  exportToExcel,
  isLoading,
  refetch,
  currentPage,
  pageSize,
  searchQuery,
  total,
  handlePageChange,
  handleSizeChange
} = usePaginationQuery({
  apiUrl: '/maidinterview/report',
  queryKeyPrefix: 'maids',
  filters
})

// 📄 Total Pages
const totalPages = computed(() => {
  return data?.value?.last_page || 1
})

function changePage(newPage) {
  if (newPage >= 1 && newPage <= totalPages.value) {
    handlePageChange(newPage)
  }
}
</script>

