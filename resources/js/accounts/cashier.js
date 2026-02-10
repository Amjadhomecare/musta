import { selectServerSideSearch } from "../reuseable/server_side_search";
import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { display_table } from "../reuseable/display_table";

$(document).ready(function () {
    const columns = [
        { data: "id", name: "id", visible: false },
        { data: "date", name: "date" },
        { data: "voucher_type", name: "voucher_type" },
        { data: "refCode", name: "refCode" },
        { data: "maid_name" },
        { data: "account", name: "account" },
        { data: "notes", name: "notes" },
        { data: "type", name: "type" },
        { data: "amount", name: "amount" },

        {
            data: "action",
            orderable: false,
            searchable: false,
        },
    ];

    display_table("/table/rv", "#general-journal-voucher-table", columns);

    selectServerSideSearch(
        "#debitLedger",
        "/add/new/ledger",
        "#cashierReceiptVoucherModal",
        "search account"
    );

    selectServerSideSearch(
        "#selected_ledger",
        "/add/new/ledger",
        "#cashierReceiptVoucherModal",
        "search credit ledger"
    );

    selectServerSideSearch(
        "#maidName",
        "/all/maids",
        "#cashierReceiptVoucherModal",
        "search maid"
    );

    handleFormPostSubmission(
        "receiptVoucherForm",
        "/store/receipt",
        "#general-journal-voucher-table",
        "#cashierReceiptVoucherModal"
    );
});
