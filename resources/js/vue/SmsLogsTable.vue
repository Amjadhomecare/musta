<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import axios from 'axios'

const search = ref('')
const page = ref(1)
const perPage = ref(10) 

// 📡 Fetch data
const fetchSmsLogs = async () => {
  const { data } = await axios.get('/datatable/sms-log', {
    params: {
      page: page.value,
      perPage: perPage.value,
      search: search.value
    }
  })
  return data
}

// 🧠 Vue Query setup
const { data, isFetching, refetch } = useQuery({
  queryKey: computed(() => ['smsLogs', page.value, perPage.value, search.value]),
  queryFn: fetchSmsLogs,
  keepPreviousData: true
})

// 🔁 Auto-refetch if search is cleared
watch(search, (val) => {
  if (val === '') refetch()
})

watch([search, page, perPage], () => {
  updateSearchURL()
})


function updateSearchURL() {
  const params = new URLSearchParams()

  if (search.value) params.set('search', search.value)
  if (page.value) params.set('page', page.value)
  if (perPage.value) params.set('perPage', perPage.value)

  const newUrl = `${window.location.pathname}?${params.toString()}`
  window.history.pushState(null, '', newUrl)
}


onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  if (params.get('search')) search.value = params.get('search')
  if (params.get('page')) page.value = parseInt(params.get('page'))
  if (params.get('perPage')) perPage.value = parseInt(params.get('perPage'))
})



function handlePageChange(val) {
  page.value = val
}

function handleSizeChange(val) {
  perPage.value = val
  page.value = 1
}

</script>

<template>
  <div class="p-4">
    <!-- 🔍 Search input -->
    <ElInput
      v-model="search"
      placeholder="🔍 Search by number or message"
      clearable
      @change="refetch"
      class="mb-4 w-80"
    />

    <p v-if="data" class="text-gray-600 mb-2">
  Total Results: {{ data.total }}
</p>
    <!-- 📋 Table -->
    <ElTable
      v-loading="isFetching"
      :data="data?.data || []"
      border
      style="width: 100%"
    >
      <ElTableColumn prop="number" label="📱 Number" />
      <ElTableColumn prop="status" label="📟 Status" />
      <ElTableColumn prop="success" label="✅ Success">
        <template #default="{ row }">{{ row.success ? '✔️' : '❌' }}</template>
      </ElTableColumn>
      <ElTableColumn prop="message" label="💬 API Response" />
      <ElTableColumn prop="created_at" label="🕒 Sent At" />
    </ElTable>

    <!-- 📄 Pagination -->
      <ElPagination
        v-if="data"
        class="mt-4"
        background
        layout="sizes, prev, pager, next"
        :current-page="page"
        :page-size="perPage"
        :total="data.total"
        :page-sizes="[10, 100, 1000]"
        @current-change="handlePageChange"
        @size-change="handleSizeChange"
      />

  </div>
</template>
