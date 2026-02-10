<template>
  <!-- Forward attrs so you can pass class, style, etc. -->
  <select ref="el" :disabled="disabled" v-bind="$attrs"></select>
</template>

<script setup>
/**
 * Requirements:
 * - jQuery and Select2 loaded globally (or imported in this component)
 * - If used in a modal, pass dropdownParent="#myModal" or a DOM element
 *
 * API contract (Laravel):
 *  {
 *    total_count: number,
 *    items: [{ id: string|number, text: string, ...anything }]
 *  }
 */

import { onMounted, onBeforeUnmount, ref, watch, nextTick } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number, null], default: null }, // v-model stores the ID
  url:        { type: String, required: true },
  placeholder:{ type: String, default: 'Select…' },
  dropdownParent: { type: [String, HTMLElement, null], default: null },
  context:    { type: [String, Number, Object, null], default: null },
  perPage:    { type: Number, default: 30 },
  minInputLength: { type: Number, default: 1 },
  allowClear: { type: Boolean, default: true },
  disabled:   { type: Boolean, default: false },

  /** Map the server JSON into Select2 results (optional override) */
  mapResults: {
    type: Function,
    default: (data, params, perPage) => ({
      results: data?.items ?? [],
      pagination: { more: (params.page || 1) * perPage < (data?.total_count || 0) }
    })
  },

  /** If you already know the current label (edit forms): { id, text } */
  preloadOption: {
    type: Object, // { id, text }
    default: null
  },

  /**
   * Optional async resolver if you only know the id on edit.
   * Should return a Promise that resolves to { id, text }.
   * Example:
   *   fetchSelected: async (id) => (await axios.get(`/maids/${id}/label`)).data
   */
  fetchSelected: {
    type: Function,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'select', 'clear', 'open', 'close'])
const el = ref(null)
let $select = null
let resolvedOnce = false // prevent double-fetch loops

function ensureOption(id, text) {
  // Ensure there's an <option> with value=id and text=text, selected.
  if (!$select) return
  // Remove any existing selected option with same value to avoid duplicates
  $select.find(`option[value="${id}"]`).remove()
  const opt = new Option(text, id, true, true)
  $select.append(opt).trigger('change')
}

async function resolveInitialLabelIfNeeded() {
  // Only run when we have an ID but no visible text option yet.
  if (!$select) return
  const id = props.modelValue
  if (id == null || id === '') return

  // If preloadOption matches, use it immediately.
  if (props.preloadOption?.id == id) {
    ensureOption(id, props.preloadOption.text ?? String(id))
    return
  }

  // If the option already exists (maybe cached by Select2), stop.
  if ($select.find(`option[value="${id}"]`).length) return

  // If fetchSelected provided, call it to get the label.
  if (typeof props.fetchSelected === 'function' && !resolvedOnce) {
    resolvedOnce = true
    try {
      // Show a temporary "Loading…" instead of the raw ID
      ensureOption(id, 'Loading…')
      const res = await props.fetchSelected(id)
      if (res && (res.id == id) && res.text) {
        ensureOption(id, res.text)
      } else {
        // Fallback: keep placeholder-ish label if fetch returns unexpected shape
        ensureOption(id, String(id))
      }
    } catch {
      // On error, fallback to showing the ID (or you can keep "Loading…")
      ensureOption(id, String(id))
    }
  } else {
    // No preload and no fetcher: fallback to showing ID or placeholder
    ensureOption(id, String(id))
  }
}

function init() {
  if (!(window.$ && $.fn?.select2)) {
    console.warn('[AjaxSelect2] jQuery + Select2 not found.')
    return
  }

  const options = {
    placeholder: props.placeholder,
    allowClear : props.allowClear,
    minimumInputLength: props.minInputLength,
    width: '100%',
    ajax: {
      url: props.url,
      dataType: 'json',
      delay: 250,
      data: (params) => ({
        search: params.term,
        page: params.page || 1,
        context: props.context
      }),
      processResults: (data, params) => props.mapResults(data, params, props.perPage),
      cache: true
    },
    templateResult: (r) => (r.loading ? r.text : r.text),
    templateSelection: (r) => r.text || r.id,
    escapeMarkup: (m) => m
  }

  if (props.dropdownParent) {
    options.dropdownParent =
      typeof props.dropdownParent === 'string'
        ? $(props.dropdownParent)
        : $(props.dropdownParent)
  }

  $select = $(el.value).select2(options)

  // If we have a current value, resolve and show the label
  resolveInitialLabelIfNeeded()

  // Events
  $select.on('change', () => {
    const val = $select.val()
    emit('update:modelValue', val && val !== '' ? val : null)
    emit('change', val)
  })

  $select.on('select2:select', (e) => emit('select', e.params.data))
  $select.on('select2:clear',  () => emit('clear'))
  $select.on('select2:open',   () => {
    emit('open')
    setTimeout(() => {
      document.querySelector('.select2-container--open .select2-search__field')?.focus()
    }, 0)
  })
  $select.on('select2:close',  () => emit('close'))
}

function destroy() {
  if ($select?.data('select2')) {
    $select.select2('destroy')
  }
  $select = null
  resolvedOnce = false
}

onMounted(() => nextTick(init))
onBeforeUnmount(destroy)

// v-model: external changes -> select2
watch(() => props.modelValue, async (val) => {
  if (!$select) return
  const current = $select.val()
  if (String(current ?? '') !== String(val ?? '')) {
    // If we already have the option, just set it
    if (val != null && $select.find(`option[value="${val}"]`).length) {
      $select.val(val).trigger('change')
      return
    }

    // If preloadOption matches, use that; else try fetchSelected; else fallback
    if (val != null) {
      if (props.preloadOption?.id == val) {
        ensureOption(val, props.preloadOption.text ?? String(val))
      } else if (typeof props.fetchSelected === 'function') {
        // temporary placeholder before fetch completes
        ensureOption(val, 'Loading…')
        try {
          const res = await props.fetchSelected(val)
          ensureOption(val, res?.text ?? String(val))
        } catch {
          ensureOption(val, String(val))
        }
      } else {
        ensureOption(val, String(val))
      }
    } else {
      $select.val(null).trigger('change')
    }
  }
})

// Re-init if key props change
watch(
  () => [props.url, props.context, props.placeholder, props.minInputLength, props.allowClear, props.dropdownParent],
  async () => {
    destroy()
    await nextTick()
    init()
  }
)
</script>
