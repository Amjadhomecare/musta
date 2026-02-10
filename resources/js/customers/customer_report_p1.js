import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { display_table } from "../reuseable/display_table";

document.addEventListener("DOMContentLoaded", () => {
    handleFormPostSubmission(
        "maidReturnForm",
        "/ajax-cat1-return",
        "#p1ContractDataTable",
        "#return-modal"
    );

    handleFormPostSubmission(
        "passportForm",
        "/passport/update",
        "#p1ContractDataTable",
        "#passport-modal"
    );

    handleFormPostSubmission(
        "editContractForm",
        "/p1-update",
        "#p1ContractDataTable",
        "#edit-modal"
    );
    const customerName = document.getElementById("customer-name").dataset.name;

    display_table(`/cont/p1/${customerName}`, "#p1ContractDataTable", [

        { data: "started_date" },
        { data: "date_return" },

        { data: "reason" },
        { data: "maid" },
        { data: "contract_ref" },

        { data: "invoice_ref" },

        { data: "amount" },
        { data: "contract_status" },
        { data: "created_by" },
        {
            data: "actions",
            name: "actions",
            orderable: false,
            searchable: false,
        },
    ]);

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
        let hasPassport = $(this).data("haspassport");

        $("#maidNameInput").val(maidName);
        $("#contractRefInput").val(contractRef);
        $("#customerInput").val(customer);
        $("#reasonInput").val("");
        $("#started_date").val(started_date);

        if (hasPassport === "yes") {
            $("#passportStatusGroup").removeClass("d-none");
            $("#passportStatus").attr("required", true);
        } else {
            $("#passportStatusGroup").addClass("d-none");
            $("#passportStatus").removeAttr("required").val("");
        }


        let startDate = new Date(started_date);
        let today = new Date();
        let dayDiff = Math.floor((today - startDate) / (1000 * 3600 * 24));
        $("#daysDifference").val("the maid worked " + dayDiff + " days");

        $("#return-modal").modal("show");
    });

    // Bootstrap 5 recommended show() usage
    $(document).on('click', '.open-passport-btn', function () {
        const refCode = $(this).data('refcode') ?? '';
        const passport = $(this).data('passport') ?? '';

        $('#passportRefCode').val(refCode);
        $('#passportStatusInput').val(passport); // exact value, no normalization

        const modalEl = document.getElementById('passport-modal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
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


$(document).ready(function () {
    $(document).on("click", ".edit-modal-btn", function () {
        const id = $(this).data("id");
        const started_date = $(this).data("started_date");
        const end_date = $(this).data("end_date");
        const return_date = $(this).data("return_date");
        const reason_note = $(this).data("return-note");

        $("#editContractId").val(id);
        $("#editStartedDate").val(started_date);
        $("#editEndDate").val(end_date);
        $("#editReturnDate").val(return_date);
        $("#editReturnNote").val(reason_note);

        $("#edit-modal").modal("show");

    });
}); 