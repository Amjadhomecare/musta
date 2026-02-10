import { display_table } from "../reuseable/display_table";


$(document).ready(function () {

display_table("/get-audit-dh", "#dh_datatable", [
    { data: "date", title: "Invoice Date" },
    {
        data: "maid_name",
        title: "DH Name",
        render: (data, type, row) =>
        `<a href="/payroll/history/${encodeURIComponent(row.maid_name)}" target="_blank">${row.maid_name}</a>`
    },
    { data: "salary", title: "DH basic salary" },
    { data: "amount", title: "Inv salary" },
    { data: "notes", title: "Note" },
    { data: "customer_name", title: "Customer Name" },
    { data: "balance", title: "Customer Balance" },
    { data: "creditNoteRef", title: "creditNoteRef" },
    { data: "refCode", title: "Refrence" },
    { data: "bank_cash", title: "Bank_cash" },
    { data: "paid_status", title: "Paid_status" },
    { data: "type", title: "Type" },
    { data: "voucher_type", title: "Voucher Type" },
    ]);



})