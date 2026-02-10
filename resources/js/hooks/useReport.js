import { reactive, ref } from "vue";
import axios from "axios";

export function useReport() {
    const loading = ref(false);
    const error = ref(null);

    const today = new Date().toISOString().split("T")[0];

    const state = reactive({
        startDate: today,
        endDate: today,
        reportData: {
            maidReturnCat1_count: 0,
            returnedMaid_count: 0,
            release_count: 0,
            arrival_count: 0,
            categoryOne_counts: [],
            category4Model_counts: [],
        },
    });

    const fetchReport = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get("/onclick-report", {
                params: {
                    start_date: state.startDate,
                    end_date: state.endDate,
                },
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
            });

            // Update report data
            Object.assign(state.reportData, response.data);
        } catch (err) {
            error.value =
                err.response?.data?.message ||
                "An error occurred while fetching the report.";
        } finally {
            loading.value = false;
        }
    };

    return {
        state,
        loading,
        error,
        fetchReport,
    };
}
