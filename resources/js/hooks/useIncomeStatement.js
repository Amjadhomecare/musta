import { ref, watch, onMounted } from "vue";

export function useIncomeStatement() {
    const today = new Date().toISOString().split("T")[0];

    // Get initial values from URL or default to today
    const urlParams = new URLSearchParams(window.location.search);
    const startDate = ref(urlParams.get("start_date") || today);
    const endDate = ref(urlParams.get("end_date") || today);
    const incomeStatement = ref(null);
    const isLoading = ref(false);
    const error = ref(null);

    // Function to fetch income statement
    const fetchIncomeStatement = async () => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(
                `/api/income-statement?start_date=${startDate.value}&end_date=${endDate.value}`
            );

            if (!response.ok) {
                throw new Error("Failed to fetch income statement");
            }

            incomeStatement.value = await response.json();

            // Update URL manually without reloading
            const newUrl = `${window.location.pathname}?start_date=${startDate.value}&end_date=${endDate.value}`;
            window.history.pushState({}, "", newUrl);
        } catch (err) {
            console.error("Error fetching income statement:", err);
            error.value = err.message || "Unknown error";
            incomeStatement.value = null;
        } finally {
            isLoading.value = false;
        }
    };

    // Watch changes and update URL dynamically
    watch([startDate, endDate], () => {
        const newUrl = `${window.location.pathname}?start_date=${startDate.value}&end_date=${endDate.value}`;
        window.history.replaceState({}, "", newUrl);
    });

    return {
        startDate,
        endDate,
        incomeStatement,
        isLoading,
        error,
        fetchIncomeStatement,
    };
}
