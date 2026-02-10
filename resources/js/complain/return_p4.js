import { display_table } from "../reuseable/display_table";

$(document).ready(function () {
    let dataTable = display_table(
        "/ajax-return-list-cat4",
        "#return-list-cat4-datatable",
        [
            {
                data: "id",
                render: function (data, type, full, meta) {
                    return `<input type="checkbox" name="ids[]" class="check-item" value="${data}">`;
                },
                orderable: false,
                searchable: false,
            },
            { data: "approval" },
            { data: "cont4" },
            { data: "created_at" },
            { data: "maid_name", name: "maid_name" },
            { data: "contract" },
            { data: "customer_name" },
            { data: "closing_balance" },
            { data: "latest_invoice_date_cat4" },
            { data: "reason" },
            { data: "category4_extra" , searchable: false, orderable: false},
            { data: "category4_note"  , searchable: false, orderable: false},

            { data: "created_by" },
            { data: "updated_by" },
        ]
    );

    // Define the approval filter function
    function approvalFilter() {
        let filter = $("#filterApproval").val();
        if (dataTable) {
            // Check if dataTable is defined
            dataTable.column(1).search(filter).draw();
        } else {
            console.error("DataTable instance is undefined.");
        }
    }

    // Attach the change event to filterApproval dropdown
    $("#filterApproval").on("change", approvalFilter);

    // Checkbox and bulk update approval logic
    $("#check-all").click(function () {
        $(".check-item").prop("checked", this.checked);
    });

    $("#bulk-update-approval").click(function () {
        let ids = $('input[name="ids[]"]:checked')
            .map(function () {
                return $(this).val();
            })
            .get();

        if (ids.length === 0) {
            alert("Please select at least one row.");
            return;
        }

        let csrfToken = $('meta[name="csrf-token"]').attr("content");

        $.ajax({
            url: "/ajax-approve-return4",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
            data: {
                ids: ids,
            },
            success: function (response) {
                alert("Approval status updated successfully!");
                dataTable.ajax.reload();
            },
            error: function () {
                alert("Error updating the approval status. Please try again.");
            },
        });
    });
});
