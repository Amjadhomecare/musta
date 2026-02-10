// resources/js/composables/usePagination.js

import { ref, watch, onMounted } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import * as XLSX from 'xlsx'

export function usePaginationQuery({ apiUrl, queryKeyPrefix, filters = {} }) {
  const searchQuery = ref('')
  const currentPage = ref(1)
  const pageSize = ref(10)
  const total = ref(0)

  // ✅ Inline manual debounce composable
  function useDebounce(value, delay = 300) {
    const debounced = ref(value.value)
    let timeout

    watch(value, (newVal) => {
      clearTimeout(timeout)
      timeout = setTimeout(() => {
        debounced.value = newVal
      }, delay)
    })

    return debounced
  }

  const debouncedSearchQuery = useDebounce(searchQuery, 700)

  // 🌐 Sync from URL on mount
  onMounted(() => {
    const params = new URLSearchParams(window.location.search)
    if (params.get('search')) searchQuery.value = params.get('search')
    if (params.get('page')) currentPage.value = parseInt(params.get('page'))
    if (params.get('perPage')) pageSize.value = parseInt(params.get('perPage'))
    for (const key in filters) {
      if (params.has(key)) filters[key].value = params.get(key)
    }
  })

  const { data, isLoading, refetch } = useQuery({
    queryKey: [queryKeyPrefix, currentPage, pageSize, debouncedSearchQuery, ...Object.values(filters)],
    queryFn: async () => {
      const params = {
        page: currentPage.value,
        per_page: pageSize.value,
        search: debouncedSearchQuery.value,
      }
      for (const key in filters) {
        params[key] = filters[key].value
      }

      const response = await axios.get(apiUrl, { params })
      total.value = response.data.total
      return response.data
    },
    keepPreviousData: true,
  })

  // ✅ Watch and update the URL only — NO manual refetch
  watch(
    [searchQuery, currentPage, pageSize, ...Object.values(filters)],
    () => {
      const params = new URLSearchParams()
      if (searchQuery.value) params.set('search', searchQuery.value)
      if (currentPage.value) params.set('page', currentPage.value)
      if (pageSize.value) params.set('perPage', pageSize.value)
      for (const key in filters) {
        if (filters[key].value) params.set(key, filters[key].value)
      }

      window.history.pushState(null, '', `${window.location.pathname}?${params.toString()}`)
    }
  )

  // ✅ Reset to first page when search or filters change
  watch(
    [searchQuery, ...Object.values(filters)],
    () => {
      if (currentPage.value !== 1) {
        currentPage.value = 1
      }
    }
  )

  const exportToExcel = () => {
    if (!data.value || !Array.isArray(data.value.data)) {
      console.warn('No data to export')
      return
    }

    const worksheet = XLSX.utils.json_to_sheet(data.value.data)
    const workbook = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Data')
    XLSX.writeFile(workbook, `${queryKeyPrefix}.xlsx`)
  }

  return {
    data,
    exportToExcel,
    isLoading,
    refetch,
    currentPage,
    pageSize,
    searchQuery,
    filters,
    total,
    handlePageChange: (val) => (currentPage.value = val),
    handleSizeChange: (val) => {
      pageSize.value = val
      currentPage.value = 1
    }
  }
}
