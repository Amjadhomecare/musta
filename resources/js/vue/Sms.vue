<!-- SendSms.vue -->
<template>
  <div class="container py-4">
    <!--begin::SMS card-->
    <div class="card shadow-sm mx-auto" style="max-width: 540px;">
      <div class="card-header py-3">
        <h5 class="card-title mb-0">Send SMS</h5>
      </div>

      <div class="card-body">
        <form @submit.prevent="sendAll" class="vstack gap-4">
          <!-- Message -->
          <div class="mb-2">
            <label class="form-label fw-semibold">Message</label>
            <textarea
              v-model="form.text"
              rows="3"
              class="form-control"
              placeholder="Type message..."
              required
            />
          </div>

          <!-- Dynamic phone numbers -->
          <div>
            <label class="form-label fw-semibold">Phone numbers</label>

            <div
              v-for="(n, i) in form.numbers"
              :key="i"
              class="d-flex align-items-start gap-2 mb-2"
            >
              <input
                v-model="form.numbers[i]"
                class="form-control"
                placeholder="05XXXXXXXX or 9715XXXXXXXX"
                required
                pattern="0?5[0-9]{8}|9715[0-9]{8}"
              />
              <button
                v-if="form.numbers.length > 1"
                type="button"
                class="btn btn-danger btn-sm"
                @click="removeInput(i)"
              >
                <i class="bi bi-dash-lg" />
              </button>
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm" @click="addInput">
              <i class="bi bi-plus-lg me-1" />Add more
            </button>
          </div>

          <!-- Progress & actions -->
          <div class="d-flex justify-content-between align-items-center mt-3">
            <button type="submit" class="btn btn-primary ms-auto" :disabled="isPending || !canSubmit">
              <span v-if="isPending" class="spinner-border spinner-border-sm me-2" role="status" />
              {{ isPending ? 'Sending…' : 'Send SMS' }}
            </button>
          </div>

          <!-- Feedback: global error -->
          <div v-if="isError" class="alert alert-danger mt-3" role="alert">
            Error: {{ error?.message || 'Request failed' }}
          </div>

          <!-- Feedback: server response summary -->
          <div v-if="server.ok !== null" :class="['alert', server.ok ? 'alert-success' : 'alert-warning', 'mt-3']" role="alert">
            {{ server.ok ? 'Queued ✔︎' : 'Some or all messages failed' }}
          </div>

          <!-- Feedback: per-number results -->
          <div v-if="server.results.length" class="mt-2">
            <div
              v-for="r in server.results"
              :key="r.number + (r.uuid || '')"
              :class="['alert', r.status ? 'alert-success' : 'alert-warning', 'py-2', 'mb-2']"
              role="alert"
            >
              <strong>{{ r.number }}</strong>
              — {{ r.status ? 'Queued' : 'Failed' }}
              <span v-if="r.message"> · {{ r.message }}</span>
              <span v-if="r.uuid"> · {{ r.uuid }}</span>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed } from 'vue'
import axios from 'axios'
import { useMutation } from '@tanstack/vue-query'

// --- form state
const form = reactive({
  text: '',
  numbers: ['']
})

// --- derived
const canSubmit = computed(() => {
  if (!form.text.trim()) return false
  return form.numbers.some(n => !!(n && n.trim()))
})

// --- server response holder (to render results easily)
const server = reactive({
  ok: null,           // null | boolean
  results: []         // [{ number, status, message, uuid }]
})

// --- vue-query mutation: call your Laravel endpoint
const { mutate, isPending, isError, error } = useMutation({
  mutationFn: (payload) => axios.post('/sms/send', payload, { timeout: 20000 }),
  retry: 1,
  onSuccess: (res) => {
    const data = res?.data || {}
    server.ok = !!data.ok
    server.results = Array.isArray(data.results) ? data.results : []
  },
  onError: () => {
    server.ok = false
    server.results = []
  }
})

// --- helpers
function addInput () { form.numbers.push('') }
function removeInput (idx) { form.numbers.splice(idx, 1) }

// --- submit handler
function sendAll () {
  const msg = form.text.trim()
  if (!msg) return

  // Send raw inputs; backend normalizes/validates (05XXXXXXXX -> 9715XXXXXXXX)
  mutate({
    text: msg,
    numbers: [...form.numbers]
  })
}
</script>

<!-- Optional: tiny style tweaks (works with Bootstrap if you already use it) -->
<style scoped>
.card-title { font-weight: 600; }
</style>
