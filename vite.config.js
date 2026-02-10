import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/js/app.js",
                "resources/js/customers/customer.js",
                "resources/js/accounts/account.js",
                "resources/js/typing/typing.js",
                "resources/js/maid/maid.js",
                "resources/js/accounts/journal_voucher.js",
                "resources/js/accounts/cashier.js",
                "resources/js/report/report.js",
                "resources/js/admin/admin.js",
                "resources/js/non_contract_inv/non_contract.js",
                "resources/js/p4/add_contractp4.js",
                "resources/js/p4/contractp4.js",
                "resources/js/p4/invoicep4.js",
                "resources/js/maid_payroll/maid_payroll.js",
                "resources/js/maid_payroll/paid_maid.js",
                "resources/js/p1/add_contractp1.js",
                "resources/js/p1/contract.js",
                "resources/js/p1/p1Invoice.js",
                "resources/js/complain/release.js",
                "resources/js/complain/release_approve.js",
                "resources/js/complain/arrival.js",
                "resources/js/customers/customer_report_p1.js",
                "resources/js/customers/p4_report.js",
                "resources/js/customers/customer_soa.js",
                "resources/js/customers/invoices.js",
                "resources/js/customers/customer_attach.js",
                "resources/js/maid/maid_report.js",
                "resources/js/maid/p1_contract.js",
                "resources/js/maid/p4_contract.js",
                "resources/js/maid/invoices.js",
                "resources/js/installment/upcoming_installment.js",
                "resources/js/accounts/jv_bulk.js",
                "resources/js/p4/audit.js",
                "resources/js/complain/return_p4.js",
                "resources/js/customers/advance.js",
                "resources/js/accounts/arrival_approval.js",
                "resources/js/customers/installment.js",
                "resources/js/complain/return_p1.js",
                "resources/js/maid_payroll/advance_maid.js",
                "resources/js/report/book_log.js",
                "resources/js/customers/adv.js",
                "resources/js/hr/p4.js",
                "resources/js/stripe/payment.js",
                "resources/js/stripe/add.js",
                "resources/js/send_note/note_to.js",
                "resources/js/send_note/note_user.js",
                "resources/js/stripe/sub.js",
                "resources/js/customers/all_branchs.js",
                "resources/js/report/worst_maid.js",
                "resources/js/customers/cus_complain.js",
                "resources/js/maid_payroll/audit_dh.js",
                "resources/js/maid_payroll/new_payroll.js",
                "resources/js/intreview/intreview.js",
                'resources/js/report/document.js',
                "resources/js/accounts/pre_connect.js",
                "resources/js/complain/refund.js",
                "resources/js/accounts/inv_connection.js",
                "resources/js/realtime/chat.js",

            ],
            refresh: true,
            build: {
                manifest: true,
                outDir: "public/build",
            },
        }),
        vue(),
    ],
    resolve: {
        alias: {
            vue: "vue/dist/vue.esm-bundler.js",
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    // Split Vue and its ecosystem into a separate chunk
                    'vue-vendor': ['vue', '@tanstack/vue-query'],

                    // Split Alpine.js into its own chunk
                    'alpine': ['alpinejs'],

                    // Split axios into its own chunk
                    'axios': ['axios'],

                    // Group utility libraries together
                    'utils': ['lodash', 'lodash-es', 'dayjs'],

                    // Split Excel/file handling libraries
                    'excel': ['xlsx'],
                },
            },
        },
        chunkSizeWarningLimit: 600, // Increase slightly to 600KB for the main app chunk
    },
});
