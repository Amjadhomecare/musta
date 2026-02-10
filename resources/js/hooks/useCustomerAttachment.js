// resources/js/hooks/useCustomerAttachment.js
import { ref } from 'vue';
import axios from 'axios';

export function useCustomerAttachment() {
  const customerSearch = ref('');
  const customerName = ref('');
  const customers = ref([]);
  const note = ref('');
  const file = ref(null);
  const message = ref('');
  const success = ref(false);
  const loading = ref(false); 
  const error = ref(''); 

  const fetchCustomer = async () => {
    if (customerSearch.value.length < 3) {
      customers.value = [];
      return;
    }

    loading.value = true;
    error.value = '';

    try {
      const response = await axios.get('/all-customers', {
        params: { search: customerSearch.value }
      });
      customers.value = response.data.items;
    } catch (err) {
      console.error('Error fetching Customer:', err);
      error.value = 'Failed to fetch Customer. Please try again later.';
    } finally {
      loading.value = false;
    }
  };

  const selectcustomer = (customer) => {
    customerName.value = customer.id;
    customerSearch.value = customer.text;
    customers.value = [];

  };

  const handleFileUpload = (event) => {
    file.value = event.target.files[0];
  };

  const uploadAttachment = async () => {
    if (!file.value || !customerName.value || !note.value) {
      message.value = 'All fields are required.';
      success.value = false;
      return;
    }

    loading.value = true;
    error.value = '';

    const formData = new FormData();
    formData.append('file', file.value);
    formData.append('customer_name', customerName.value);
    formData.append('note', note.value);

    try {
      const response = await axios.post('/customer/upload-attachment', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });

      message.value = response.data.message;
      success.value = true;
    } catch (err) {
      console.error('Error uploading file:', err);
      message.value = 'Failed to upload file.';
      success.value = false;
      error.value =err ;
    } finally {
      loading.value = false;
    }
  };

  return {
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
    uploadAttachment,
  };
}
