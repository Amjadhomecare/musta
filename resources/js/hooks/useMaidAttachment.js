// resources/js/hooks/useMaidAttachment.js
import { ref, watch, computed } from "vue";
import useSWRV from "swrv";
import axios from "axios";
import debounce from "lodash/debounce";

export function useMaidAttachDataTable() {
  const maidSearch = ref("");
  const status = ref("");
  const page = ref(1);
  const perPage = ref(10);
  const url = ref("/table/attach/maid");

  const fetcher = (url) => axios.get(url).then((res) => res.data);

  const key = computed(() => {
    const params = new URLSearchParams();
    if (maidSearch.value) params.set("search", maidSearch.value);
    params.set("page", String(page.value));
    params.set("per_page", String(perPage.value));
    if (status.value) params.set("status", status.value);
    return `${url.value}?${params.toString()}`;
  });

  const { data, error, mutate, isValidating } = useSWRV(
    () => key.value,
    fetcher,
    {
      revalidateOnFocus: false, // Don't refetch when window regains focus
      revalidateOnReconnect: false, // Don't refetch when network reconnects
      dedupingInterval: 2000 // Prevent duplicate requests within 2 seconds
    }
  );

  const searchMaids = debounce(() => {
    page.value = 1;
    // No need to call mutate() - SWRV will automatically refetch when key changes
  }, 700);

  const changePage = (newPage) => {
    page.value = newPage;
    // No need to call mutate() - SWRV will automatically refetch when key changes
  };

  watch(maidSearch, (newQuery) => {
    // Only trigger when >=3 chars or cleared
    if (newQuery.length >= 3 || newQuery.length === 0) {
      searchMaids();
    }
  });

  return {
    maidSearch,
    status,
    page,
    perPage,
    data,
    error,
    isValidating,
    searchMaids,
    changePage,
    mutate,
  };
}

export function useMaidAttachment() {
  const maidSearch = ref("");
  const maidName = ref(""); // IMPORTANT: keep the *name* string (not ID)
  const maids = ref([]);
  const note = ref("");
  const file = ref(null);
  const fileList = ref([]);
  const message = ref("");
  const success = ref(false);
  const loading = ref(false);
  const error = ref("");
  const uploading = ref(false);
  const uploadProgress = ref(0);

  const fetchMaids = async (query) => {
    if (query.length < 3) {
      maids.value = [];
      return;
    }

    loading.value = true;
    error.value = "";

    try {
      const response = await axios.get("/all/maids", { params: { search: query } });
      // Expecting array like [{ id, name, ... }]
      maids.value = response.data.items || [];
    } catch (err) {
      error.value = "Failed to fetch maids. Please try again later.";
    } finally {
      loading.value = false;
    }
  };

  const debounceFetchMaids = debounce(fetchMaids, 300);

  // When user selects a maid from a dropdown/autocomplete:
  // Keep the *name* to comply with the controller ("maid_name")
  const selectMaid = (maidId) => {
    const selectedMaid = maids.value.find((m) => m.id === maidId);
    maidName.value = selectedMaid ? selectedMaid.name : "";
  };

  const handleFileUpload = (uploadFile) => {
    if (uploadFile && uploadFile.raw) {
      file.value = uploadFile.raw;
      fileList.value = [uploadFile];
    } else {
      file.value = null;
      fileList.value = [];
    }
  };

  const uploadAttachment = async () => {
    if (!file.value || !maidName.value || !note.value) {
      message.value = "All fields are required.";
      success.value = false;
      return;
    }

    loading.value = true;
    uploading.value = true;
    uploadProgress.value = 0;
    error.value = "";

    const formData = new FormData();
    formData.append("file", file.value);
    formData.append("maid_name", maidName.value); // keep string name
    formData.append("note", note.value);

    try {
      const response = await axios.post("/maids/upload-attachment", formData, {
        headers: { "Content-Type": "multipart/form-data" },
        onUploadProgress: (e) => {
          if (e.total) uploadProgress.value = Math.round((e.loaded * 100) / e.total);
        },
      });

      message.value = response.data.message || "Uploaded successfully.";
      success.value = true;

      // Emit custom event to trigger table refresh
      window.dispatchEvent(new CustomEvent('maid-attachment-uploaded'));

      // Reset form after successful upload
      file.value = null;
      fileList.value = [];
      note.value = "";
      maidName.value = "";
      maidSearch.value = "";
    } catch (err) {
      message.value = "Failed to upload file.";
      success.value = false;
      error.value = "File upload failed. Please try again later.";
    } finally {
      loading.value = false;
      uploading.value = false;
    }
  };

  return {
    maidSearch,
    maidName,
    maids,
    note,
    file,
    fileList,
    message,
    success,
    loading,
    error,
    uploading,
    uploadProgress,
    debounceFetchMaids,
    selectMaid,
    handleFileUpload,
    uploadAttachment,
  };
}

export function useDeleteMaidAttachment() {
  const deleteMaidAttachment = async (id) => {
    try {
      const response = await axios.post("/maids/delete-attachment", { attachment_id: id });
      if (response.data.status === "success") {
        // Emit custom event to trigger table refresh
        window.dispatchEvent(new CustomEvent('maid-attachment-uploaded'));
        alert("Attachment deleted successfully");
      } else {
        alert(response.data.message || "Failed to delete attachment");
      }
    } catch (err) {
      console.error("Error deleting attachment:", err);
      alert("Failed to delete attachment");
    }
  };

  return { deleteMaidAttachment };
}
