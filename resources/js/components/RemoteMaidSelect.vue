<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import { useQuery } from '@tanstack/vue-query'

/**
 * Special remote select for the Al Ahlia Maids search API.
 * - v-model returns the maid's NAME by default (item.text).
 * - Emits 'select' with the full maid object so the parent can store it in extra_data.
 */
const props = defineProps({
  modelValue: [String, Number, Object],
  placeholder: { type: String, default: 'Search maids by name, passport…' },
  minSearchLength: { type: Number, default: 2 },
  debounceTime: { type: Number, default: 300 },
  /** what to put into v-model: 'text' | 'id' | 'object' */
  returnField: { type: String, default: 'text' }
})

const emit = defineEmits(['update:modelValue', 'select'])

const selected = ref(props.modelValue ?? '')
const search = ref('')
let timer = null

watch(() => props.modelValue, v => { selected.value = v })

function handleSearch(q) {
  if (timer) clearTimeout(timer)
  timer = setTimeout(() => {
    search.value = (q ?? '').trim()
  }, props.debounceTime)
}

const fetchMaids = async () => {
  if (!search.value || search.value.length < props.minSearchLength) return []
  const { data } = await axios.get('https://api.alahliamaids.com/MaidsSearch', {
    params: { query: search.value }
  })
  return Array.isArray(data) ? data : []
}

const { data: options, isFetching, refetch } = useQuery({
  queryKey: () => ['maids-remote', search.value],
  queryFn: fetchMaids,
  enabled: false
})

watch(search, val => { if (val.length >= props.minSearchLength) refetch() })

function onChange(val) {
  // resolve full selected item for 'select' emit
  let item = null
  if (props.returnField === 'object' && val && typeof val === 'object') {
    item = val
  } else {
    item = (options.value || []).find(o =>
      props.returnField === 'id' ? o.id === val
      : props.returnField === 'text' ? o.text === val
      : false
    ) || null
  }
  if (item) emit('select', item)
  emit('update:modelValue', val)
}
</script>

<template>
  <ElSelect
    v-model="selected"
    filterable
    remote
    clearable
    reserve-keyword
    :placeholder="placeholder"
    :remote-method="handleSearch"
    :loading="isFetching"
    class="w-100"
    @change="onChange"
  >
    <ElOption
      v-for="item in options || []"
      :key="item.id"
      :label="item.text"
      :value="returnField === 'object' ? item : (returnField === 'id' ? item.id : item.text)"
    >
      <div class="d-flex flex-column">
        <span class="fw-semibold">{{ item.text }}</span>
        <small class="text-muted">
          Passport: {{ item.passport || '—' }}
          • {{ item.nationality || '—' }}
          <template v-if="item.salary"> • Salary: {{ item.salary }}</template>
          <template v-if="item.branch"> • Branch: {{ item.branch }}</template>
        </small>
      </div>
    </ElOption>
  </ElSelect>
</template>
