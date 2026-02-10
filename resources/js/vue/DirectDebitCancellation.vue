<template>
  <div class="card">
    <div class="card-header border-0 pt-6">
      <div class="card-title">
        <h3>Direct Debit Cancellations</h3>
      </div>
      <div class="card-toolbar">
         <!-- Toolbar options if needed -->
      </div>
    </div>
    <div class="card-body py-4">
      <div class="table-responsive">
        <table class="table align-middle table-row-dashed fs-6 gy-5">
          <thead>
            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
              <th>Ref</th>
              <th>Account Title</th>
              <th>Customer ID</th>
              <th>Note</th>
              <th>Status</th>
              <th>Active</th>
              <th>Created By</th>
              <th>Created At</th>
            </tr>
          </thead>
          <tbody class="text-gray-600 fw-semibold">
            <tr v-if="isLoading">
              <td colspan="8" class="text-center">Loading...</td>
            </tr>
            <tr v-else-if="!data || data.data.length === 0">
              <td colspan="8" class="text-center">No records found</td>
            </tr>
            <tr v-for="(item, index) in data?.data" :key="index">
              <!-- Access direct_debit relationship -->
             <td>
                <a :href="`/vue/direct-debit?search=${item.direct_debit?.ref}`">
                  {{ item.direct_debit?.ref }}
                </a>
              </td>
              <td>{{ item.direct_debit?.account_title }}</td>
              <td>{{ item.direct_debit?.customer?.name }}</td>
              <td>{{ item.note }}</td>
              <td>
                <span :class="getStatusClass(item.status)">
                  {{ getStatusLabel(item.status) }}
                </span>
              </td>
              <td>
                <span :class="item.direct_debit?.active === 0 ? 'badge badge-light-success' : 'badge badge-light-danger'">
                  {{ item.direct_debit?.active === 0 ? 'Active' : 'Cancelled' }}
                </span>
              </td>
              <td>{{ item.created_by_user?.name }}</td>
              <td>
                  <div>Created: {{ formatDate(item.created_at) }}</div>
                  <div class="text-muted fs-7">DD Created: {{ formatDate(item.direct_debit?.created_at) }}</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="data && data.links" class="row">
        <div class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start">
           <!-- Info text -->
        </div>
        <div class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end">
             <ul class="pagination">
                <li v-for="(link, i) in data.links" :key="i" class="page-item" :class="{ disabled: !link.url, active: link.active }">
                  <a class="page-link" href="#" @click.prevent="fetchData(link.url)" v-html="link.label"></a>
                </li>
             </ul>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
  name: 'DirectDebitCancellation',
  setup() {
    const data = ref(null);
    const isLoading = ref(false);

    const fetchData = async (url = '/debit-cancellation') => {
      isLoading.value = true
      try {
        const response = await axios.get(url);
        data.value = response.data;
      } catch (error) {
        console.error('Error fetching data:', error);
      } finally {
        isLoading.value = false;
      }
    };

    const getStatusLabel = (status) => {
      const map = {
        1: 'Requested',
        2: 'Pending',
        3: 'Approved'
      };
      return map[status] || status;
    };

    const getStatusClass = (status) => {
        // adjust colors as needed
        if (status === 1) return 'badge badge-light-warning';
        if (status === 2) return 'badge badge-light-primary';
        if (status === 3) return 'badge badge-light-success';
        return 'badge badge-light-secondary';
    };

    const formatDate = (dateString) => {
      if (!dateString) return '';
      return new Date(dateString).toLocaleString();
    };

    onMounted(() => {
      fetchData();
    });

    return {
      data,
      isLoading,
      fetchData,
      getStatusLabel,
      getStatusClass,
      formatDate
    };
  }
}
</script>
