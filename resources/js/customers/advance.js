import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { display_table } from "../reuseable/display_table";
import { selectServerSideSearch } from "../reuseable/server_side_search";

document.addEventListener("DOMContentLoaded", function () {
    handleFormPostSubmission(
        "advanceForm",
        "/store-ar-ads",
        "#advance_datatable",
        "#advance-modal"
    );

    handleFormPostSubmission(
        "receiveAdvanceForm",
        "/rv-advance",
        "#advance_datatable",
        "#receive-advance-modal"
    );

    display_table("/table-ads", "#advance_datatable", [
        { data: "date" },
        { data: "customer" },
        { data: "phone_number" },
        { data: "maid" },
        { data: "post_type" },
        { data: "note" },
        { data: "amount" },
        { data: "ref" },
        { data: "received" },

        { data: "created_by" },
        { data: "updated_by" },
        {
            data: "created_at",
            render: function (data) {
                return new Date(data).toLocaleString();
            },
        },
        {
            data: "updated_at",
            render: function (data) {
                return new Date(data).toLocaleString();
            },
        },
        { data: "action" },
    ]);

    const advanceModalButton = document.querySelector(".advance-modal-btn");

    const advanceModal = new bootstrap.Modal(
        document.getElementById("advance-modal")
    );

    advanceModalButton.addEventListener("click", function () {
        const today = new Date().toISOString().substring(0, 10);
        $('input[name="date"]').val(today);
        advanceModal.show();

        selectServerSideSearch(
            "#customerSelect",
            "/all-customers",
            "#advance-modal",
            "Search customer"
        );

        selectServerSideSearch(
            "#maidSelect",
            "/all/maids",
            "#advance-modal",
            "Search Maid"
        );
    });

    $(document).on("click", ".receive-advance-btn", function () {
        const advanceId = $(this).data("id");
        $.ajax({
            url: "/ads/" + advanceId,
            method: "GET",
            success: function (data) {
                const today = new Date().toISOString().substring(0, 10);

                $("#customerAdvanceId").val(data.id);
                $("#customerName").val(data.customer_info?.name);
                $("#maidName").val(data.maid_info?.name);
                $("#advanceAmount").val(data.amount);
                $("#receiveAmount").attr("max", data.amount);
                $("#receiveNotes").val(data.note);
                $('input[name="date"]').val(today);
                $("#receive-advance-modal").modal("show");

                selectServerSideSearch(
                    "#receivedMehtod",
                    "/add/new/ledger",
                    "#receive-advance-modal",
                    "search account"
                );
            },
            error: function () {
                alert("Failed to fetch advance details");
            },
        });
    });
});
