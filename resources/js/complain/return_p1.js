import { display_table } from "../reuseable/display_table";

$(document).ready(function () {
    let dataTable = display_table(
        "/ajax-return-list-cat1",
        "#return-list-cat4-datatable",
        [
            {
                data: "checkbox",
                orderable: false,
                searchable: false,
            },
            { data: "approval" },
            {
                data: "refund",
                render: function (data) {
                    return data.trim();
                },
            },
            {
                data: "created_at",
                render: function (data) {
                    let date = new Date(data);
                    return date.toLocaleString();
                },
            },
            { data: "maid_name" },
            { data: "contract" },
            { data: "invoice" },
            {
                data: "customer_name",
                orderable: false,
            },
            {
                data: "latest_invoice_date_cat1",
                orderable: false,
                searchable: false,
            },
            {
                data: "latest_invoice_amount_cat1",
                orderable: false,
                searchable: false,
            },

            {
                data: "closing_balance",
                orderable: false,
                searchable: false,
            },
            { data: "reason" },
            { data: "created_by" },
            { data: "updated_by" },
        ]
    );

    function applyFilters() {
        let filterApproval = $("#filterApproval").val();
        let filterRefund = $("#filterRefund").val();

        dataTable.column(1).search(filterApproval);
        dataTable.column(2).search(filterRefund);

        dataTable.draw();
    }

    $("#filterApproval").on("change", applyFilters);
    $("#filterRefund").on("change", applyFilters);

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
            url: "/ajax-approve-return1",
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
