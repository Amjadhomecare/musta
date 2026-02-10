<template>
    <div class="container my-4">
        <!-- Card for Inputs -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Filter Income Statement</h5>
                <div class="row">
                    <!-- Start Date Input -->
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label"
                            >Start Date:</label
                        >
                        <input
                            type="date"
                            v-model="startDate"
                            id="start_date"
                            class="form-control"
                        />
                    </div>

                    <!-- End Date Input -->
                    <div class="col-md-6 mb-3">
                        <label for="end_date" class="form-label"
                            >End Date:</label
                        >
                        <input
                            type="date"
                            v-model="endDate"
                            id="end_date"
                            class="form-control"
                        />
                    </div>
                </div>

                <!-- Fetch Button -->
                <div class="text-end">
                    <button
                        @click="fetchIncomeStatement"
                        class="btn btn-warning text-dark"
                    >
                        Fetch Income Statement
                    </button>
                </div>
            </div>
        </div>

        <!-- Card for Displaying Income Statement -->
        <div class="card" v-if="incomeStatement">
            <div class="card-body">
                <h5 class="card-title">Income Statement Results</h5>

                <!-- Display Revenues -->
                <div class="mb-4">
                    <h6 class="card-subtitle mb-2 text-muted">Revenues</h6>
                    <div
                        v-for="(revenue, key) in incomeStatement.revenues"
                        :key="key"
                    >
                        <strong>{{ key }}</strong>
                        <p>Total: {{ revenue.total }}</p>
                        <ul>
                            <li
                                v-for="(amount, ledger) in revenue.ledgers"
                                :key="ledger"
                            >
                                {{ ledger }}: {{ amount }}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Display Expenses -->
                <div class="mb-4">
                    <h6 class="card-subtitle mb-2 text-muted">Expenses</h6>
                    <div
                        v-for="(expense, key) in incomeStatement.expenses"
                        :key="key"
                    >
                        <strong>{{ key }}</strong>
                        <p>Total: {{ expense.total }}</p>
                        <ul>
                            <li
                                v-for="(amount, ledger) in expense.ledgers"
                                :key="ledger"
                            >
                                {{ ledger }}: {{ amount }}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Display Totals -->
                <div>
                    <h6 class="card-subtitle mb-2 text-muted">Summary</h6>
                    <p>Total Revenue: {{ incomeStatement.total_revenue }}</p>
                    <p>Total Expenses: {{ incomeStatement.total_expenses }}</p>
                    <p>Net Income: {{ incomeStatement.net_income }}</p>
                </div>
            </div>
        </div>

        <!-- Loading and Prompt (when no results yet) -->
        <div class="card" v-else>
            <div class="card-body">
                <p v-if="isLoading">Loading...</p>
                <p v-else-if="error">{{ error }}</p>
                <p v-else>
                    Please select dates and click "Fetch Income Statement".
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useIncomeStatement } from "/resources/js/hooks/useIncomeStatement";

const {
    startDate,
    endDate,
    incomeStatement,
    isLoading,
    error,
    fetchIncomeStatement,
} = useIncomeStatement();
</script>
