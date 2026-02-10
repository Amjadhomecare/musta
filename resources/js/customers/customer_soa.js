import { display_table } from "../reuseable/display_table";
import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { ajaxSelector } from "../ajax_select2";

$(document).ready(function () {
    const customerName = $("#customer-name").data("name");

    display_table(`/soa/customer/${customerName}`, "#datatable_soa", [
        { data: "created_at", visible: false }, 
        { data: "date" },
        { data: "voucher_type" },
        { data: "refCode" },
        { data: "pre_connection_name" },
        { data: "notes" },
        { data: "maid_name" },
        { data: "debit" },
        { data: "credit" },
        { data: "running_balance" },
    ]);

    ajaxSelector(
        "#otherLedger",
        "/add/new/ledger",
        "search ledger",
        "#makeJVModal"
    );

    

    ajaxSelector(
        "#maid_name",
        "/all/maids",
        "search maid",
        "#makeJVModal"
    );

    handleFormPostSubmission(
        "makeJVForm",
        "/customer/jv",
        "#datatable_soa",
        "#makeJVModal"
    );
});
