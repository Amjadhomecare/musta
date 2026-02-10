<template>
  <div class="container my-4">
    <!-- Card for File Upload Form -->
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title">Upload Customer Attachment</h5>
        <form @submit.prevent="uploadAttachment">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="customer-name" class="form-label">Customer Name:</label>
              <input
                v-model="customerSearch"
                @input="fetchCustomer"
                class="form-control"
                id="customer-name"
                placeholder="Search for a customer..."
              />
              <ul v-if="customers.length && !loading" class="list-group mt-2">
                <li
                  v-for="customer in customers"
                  :key="customer.id"
                  @click="selectcustomer(customer)"
                  class="list-group-item"
                >
                  {{ customer.text }}
                </li>
              </ul>
              <p v-if="loading" class="text-muted">Loading customers...</p>
              <p v-if="error" class="text-danger">{{ error }}</p>
            </div>

            <!-- Note Input -->
            <div class="col-md-6 mb-3">
              <label for="note" class="form-label">Note:</label>
              <textarea
                v-model="note"
                id="note"
                class="form-control"
                rows="3"
                required
              ></textarea>
            </div>
          </div>

          <!-- File Upload Input -->
          <div class="mb-3">
            <label for="file" class="form-label">File:</label>
            <input
              type="file"
              id="file"
              class="form-control"
              @change="handleFileUpload"
              required
            />
          </div>

          <!-- Submit Button -->
          <div class="text-end">
            <button type="submit" class="btn btn-primary" :disabled="loading">
              Upload
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Card for Feedback Messages -->
    <div v-if="message" class="card">
      <div
        :class="{
          'alert-success': success,
          'alert-danger': !success,
        }"
        class="alert"
      >
        {{ message }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { useCustomerAttachment } from '/resources/js/hooks/useCustomerAttachment';

const {
  customerSearch,
  customerName,
  customers,
  note,
  file,
  message,
  success,
  loading,
  error,
  fetchCustomer,
  selectcustomer,
  handleFileUpload,
  uploadAttachment
} = useCustomerAttachment();
</script>

<style scoped>
.container {
  max-width: 800px;
}

.card {
  margin-bottom: 20px;
}

.alert {
  margin-top: 20px;
}
</style>
