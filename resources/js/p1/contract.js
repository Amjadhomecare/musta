// resources/js/p1/contract.js
import { display_table } from "../reuseable/display_table";
import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";

$(document).ready(function () {
    handleFormPostSubmission(
        "maidReturnForm",
        "/ajax-cat1-return",
        "#p1ContractDataTable",
        "#return-modal"
    );
    const columns = [
        { data: "contract_ref" },
        { data: "started_date" },
        { data: "invoice_ref" },
        { data: "customer" },
        { data: "phone" },
        { data: "maid" },
        { data: "amount" },
        { data: "contract_status" },
        { data: "created_by" },
        {
            data: "actions",
            name: "actions",
            orderable: false,
            searchable: false,
        },
    ];

    display_table("/ajax-cat1", "#p1ContractDataTable", columns);

    function applyFilters() {
        let filterContract = $("#filterContracts").val();

        $("#p1ContractDataTable")
            .DataTable()
            .column(7)
            .search(filterContract)
            .draw();
    }

    $("#filterContracts").on("change", applyFilters);

    $("#example").on("click", ".delete-sign", function () {
        const id = $(this).data("id");

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/delete_sign/cat1/${id}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                        "Content-Type": "application/json",
                    },
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error("Network response was not ok");
                        }
                        return response.json();
                    })
                    .then((data) => {
                        Swal.fire({
                            title: data.success ? "Deleted!" : "Error!",
                            text: data.message,
                            icon: data.success ? "success" : "error",
                        });
                        if (data.success) {
                            $("#example").DataTable().ajax.reload();
                        }
                    })
                    .catch((error) => {
                        console.error("Error:", error);
                        Swal.fire({
                            title: "Error!",
                            text: "Failed to delete the signature.",
                            icon: "error",
                        });
                    });
            }
        });
    });
});

$(document).ready(function () {
    $(document).on("click", ".open-modal-btn", function () {
        let maidName = $(this).data("maid");
        let contractRef = $(this).data("contractref");
        let customer = $(this).data("customer");
        let started_date = $(this).data("started_date");
        $("#maidNameInput").val(maidName);
        $("#contractRefInput").val(contractRef);
        $("#customerInput").val(customer);
        $("#reasonInput").val("");
        $("#started_date").val(started_date);

        let startedDate = document.getElementById("started_date").value;

        let today = new Date();
        let startDate = new Date(startedDate);
        let timeDiff = today.getTime() - startDate.getTime();
        let dayDiff = Math.floor(timeDiff / (1000 * 3600 * 24));
        document.getElementById("daysDifference").value =
            "the maid worked  " + dayDiff + "  days";

        $("#signup-modal").modal("show");
    });

    $("#p1ContractDataTable").on("click", ".delete-sign", function () {
        const id = $(this).data("id");

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/delete_sign/cat1/${id}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                        "Content-Type": "application/json",
                    },
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error("Network response was not ok");
                        }
                        return response.json();
                    })
                    .then((data) => {
                        Swal.fire({
                            title: data.success ? "Deleted!" : "Error!",
                            text: data.message,
                            icon: data.success ? "success" : "error",
                        });
                        if (data.success) {
                            $("#p1ContractDataTable").DataTable().ajax.reload();
                        }
                    })
                    .catch((error) => {
                        console.error("Error:", error);
                        Swal.fire({
                            title: "Error!",
                            text: "Failed to delete the signature.",
                            icon: "error",
                        });
                    });
            }
        });
    });
});
