import { display_table } from "../reuseable/display_table";
import { selectServerSideSearch } from "../reuseable/server_side_search";
import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";

document.addEventListener("DOMContentLoaded", function () {
    const maidName = document.getElementById("maid-data").dataset.name;

    display_table(`/maid-finance/${maidName}`, "#maid_finance", [
        { data: "id" },
        { data: "date" },
        { 
            data: "refCode", 
            render: function(data, type, row) {
                if (type === 'display') {
                    return `<a href="/view/jv/selected/${data}" target="_blank">${data}</a>`;
                }
                return data; 
            }
        },
        { data: "voucher_type" },
        { data: "type" },
        { data: "account_ledger.ledger" },
        { data: "amount" },
        { data: "notes" },
        { data: "created_by" },
        { data: "updated_by" },
        { data: "created_at" },
    ]);

    function applyFilters() {
        let filter = $("#voucher_type").val();
        let voucherType = $("#vt").val();

        $("#maid_finance")
            .DataTable()
            .column(4)
            .search(voucherType)
            .column(3)
            .search(filter)
            .draw();
    }

    $("#voucher_type,#vt").on("change", applyFilters);


      selectServerSideSearch(
            "#debitLedger",
            "/add/new/ledger",
            "#makeJVModal",
            "search debit ledger"
        );
    
        selectServerSideSearch(
            "#creditledger",
            "/add/new/ledger",
            "#makeJVModal",
            "search credit ledger"
        ); 


        handleFormPostSubmission(
            "makeJVForm",
            "/jv/maid",
            "#maid_finance",
            "#makeJVModal"
        );
});
