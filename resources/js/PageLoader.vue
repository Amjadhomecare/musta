<template>
  <component :is="pageComponent" v-if="pageComponent" />
  <div v-else class="text-red-600 p-4">❌ Component not found for "{{ pageName }}".</div>
</template>

<script setup>
import { computed } from 'vue'
import pageMap from './pageMap'

const pageName = document.getElementById('vue-app')?.dataset.page
const pageComponent = computed(() => pageMap[pageName] || null)
</script>
