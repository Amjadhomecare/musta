//  resources/js/components/RemoteSelect.vue

<script setup>
import { ref, watch } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import axios from 'axios'

const props = defineProps({
  modelValue: [String, Number],
  name: String,
  apiUrl: { type: String, required: true },
  placeholder: { type: String, default: 'Select option' },
  searchParam: { type: String, default: 'search' },
  labelKey: { type: String, default: 'text' },
  valueKey: { type: String, default: 'id' },
  minSearchLength: { type: Number, default: 2 },
  debounceTime: { type: Number, default: 300 },
  page: { type: Number, default: 1 }
})

const emit = defineEmits(['update:modelValue'])

const search = ref('')
const showDropdown = ref(false)
const selectedLabel = ref('')
let searchTimer = null

// Initialize selected label if modelValue exists
if (props.modelValue) {
  selectedLabel.value = props.modelValue
}

watch(() => props.modelValue, val => {
  if (val) {
    selectedLabel.value = val
  }
})

const fetchOptions = async () => {
  const { data } = await axios.get(props.apiUrl, {
    params: {
      [props.searchParam]: search.value,
      page: props.page
    }
  })
  return data.items
}

const {
  data: options,
  isFetching,
  refetch,
} = useQuery({
  queryKey: () => [props.apiUrl, search.value],
  queryFn: fetchOptions,
  enabled: false,
})

// Debounced search
function handleInput(event) {
  const query = event.target.value
  
  if (searchTimer) clearTimeout(searchTimer)
  
  searchTimer = setTimeout(() => {
    search.value = query
    if (query.length >= props.minSearchLength) {
      showDropdown.value = true
      refetch()
    } else {
      showDropdown.value = false
    }
  }, props.debounceTime)
}

// Select an item
function selectItem(item) {
  const value = item[props.valueKey]
  selectedLabel.value = item[props.labelKey]
  showDropdown.value = false
  search.value = ''
  
  // Emit the value
  emit('update:modelValue', value)
}

// Clear selection
function clearSelection() {
  selectedLabel.value = ''
  showDropdown.value = false
  search.value = ''
  emit('update:modelValue', null)
}

// Handle focus
function handleFocus() {
  if (search.value.length >= props.minSearchLength) {
    showDropdown.value = true
  }
}
</script>

<template>
  <div class="remote-select-container position-relative" @click.stop>
    <div class="input-group">
      <input
        :value="selectedLabel || search"
        type="text"
        class="form-control"
        :placeholder="placeholder"
        @input="handleInput"
        @focus="handleFocus"
        autocomplete="off"
      />
      
      <!-- Clear button -->
      <button
        v-if="selectedLabel"
        type="button"
        class="btn btn-outline-secondary"
        @click="clearSelection"
        title="Clear selection"
      >
        <i class="ki-duotone ki-cross fs-3">
          <i class="path1"></i>
          <i class="path2"></i>
        </i>
      </button>

      <!-- Loading indicator -->
      <span v-if="isFetching" class="input-group-text">
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
      </span>
      
      <!-- Search icon -->
      <span v-else class="input-group-text">
        <i class="ki-duotone ki-magnifier fs-3">
          <i class="path1"></i>
          <i class="path2"></i>
        </i>
      </span>
    </div>

    <!-- Dropdown menu -->
    <div
      v-if="showDropdown && (options?.length || isFetching)"
      class="dropdown-menu show w-100 mt-1 shadow"
      style="max-height: 250px; overflow-y: auto; position: absolute; z-index: 1050;"
    >
      <!-- Loading state -->
      <div v-if="isFetching" class="dropdown-item-text text-center py-3">
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Loading...
      </div>

      <!-- Options -->
      <button
        v-else
        v-for="item in options"
        :key="item[valueKey]"
        type="button"
        class="dropdown-item d-flex align-items-center py-2"
        :class="{ 'active': selectedLabel === item[labelKey] }"
        @click="selectItem(item)"
      >
        <i v-if="selectedLabel === item[labelKey]" class="ki-duotone ki-check fs-3 me-2 text-success">
          <i class="path1"></i>
          <i class="path2"></i>
        </i>
        <span>{{ item[labelKey] }}</span>
      </button>

      <!-- No results -->
      <div v-if="!isFetching && options?.length === 0" class="dropdown-item-text text-center text-muted py-3">
        <i class="ki-duotone ki-information-2 fs-1 d-block mb-2">
          <i class="path1"></i>
          <i class="path2"></i>
          <i class="path3"></i>
        </i>
        No results found
      </div>
    </div>

    <!-- Hidden input for form submission -->
    <input v-if="name" type="hidden" :name="name" :value="selectedLabel" />
  </div>
</template>

<style scoped>
/* Theme-adaptive styling */
.remote-select-container {
  width: 100%;
}

.form-control {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
  color: var(--bs-body-color);
}

.form-control:focus {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-primary);
  color: var(--bs-body-color);
  box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
}

.input-group-text {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
  color: var(--bs-body-color);
}

.btn-outline-secondary {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
  color: var(--bs-body-color);
}

.btn-outline-secondary:hover {
  background-color: var(--bs-tertiary-bg);
  border-color: var(--bs-border-color);
  color: var(--bs-body-color);
}

.dropdown-menu {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
}

.dropdown-item {
  color: var(--bs-body-color);
}

.dropdown-item:hover {
  background-color: var(--bs-tertiary-bg);
  color: var(--bs-body-color);
}

.dropdown-item.active {
  background-color: var(--bs-primary-bg-subtle);
  color: var(--bs-primary);
}

.dropdown-item-text {
  color: var(--bs-body-color);
}

/* Remove default button styles from dropdown items */
.dropdown-item {
  border: none;
  text-align: left;
  width: 100%;
}
</style>
