import { display_table } from "../reuseable/display_table";
import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { selectServerSideSearch } from "../reuseable/server_side_search";

$(document).ready(function () {
    handleFormPostSubmission(
        "maidReturnForm",
        "/add/return/action",
        "#p4dataTable",
        "#return_modal"
    );
    handleFormPostSubmission(
        "customerCompForm",
        "/post/complaint",
        "#p4dataTable",
        "#comp_modal"
    );

    handleFormPostSubmission(
        "startDateForm",
        "/update/date/p4",
        "#p4dataTable",
        "#start_date_modal"
    );

    const columns = [
        { data: "date", name: "date" },
        { data: "Contract_ref" },
        { data: "customer" },
        { data: "phone" },
        { data: "maid" },
        { data: "contract_status", name: "contract_status" },
        { data: "created_by" },
        { data: "action", name: "action", orderable: false, searchable: false },
    ];

    display_table("/category4/data", "#p4dataTable", columns);

    $('#no_image').change(function () {
        let no_image = $(this).is(':checked') ? 'true' : 'false';
    
        $('#p4dataTable')
            .DataTable()
            .ajax.url('/category4/data?no_image=' + no_image )
            .load();
    });
    

    function applyFilters() {
        let filterContract = $("#filterContracts").val();

        $("#p4dataTable").DataTable()
        .column(5)
        .search(filterContract).draw();
    }

    $("#filterContracts").on("change", applyFilters);

    $(document).on("click", ".edit-date-open-modal-btn", function () {
        let id = $(this).data("id");
        console.log("Button clicked! ID:", id);

        if (!id) {
            console.error("No ID found!");
            return;
        }

        $.ajax({
            url: "/p4/get/date/" + id,
            type: "GET",
            success: function (data) {
                $("#start_date_modal").modal("show");
                $("#idContract").val(data.response.id);
                $("#startDatenput").val(data.response.date);
                $("#returnDatenput").val(data.response?.return_info?.returned_date);

            },
            error: function (xhr) {
                console.error("Error during AJAX:", xhr);
            },
        });
    });
});

$(document).ready(function () {
    $(document).on("click", ".open-modal-btn", function () {
        let maidName = $(this).data("maid");
        let contractRef = $(this).data("contractref");
        let customer = $(this).data("customer");
        $("#maidNameInput").val(maidName);
        $("#contractRefInput").val(contractRef);
        $("#customerInput").val(customer);
        $("#reasonInput").val("");

        $("#signup-modal").modal("show");
    });
});

$(document).ready(function () {
    $(document).on("click", ".open-comp-modal-btn", function () {
        let maidName = $(this).data("maid");
        let contractRef = $(this).data("contractref");
        let customer = $(this).data("customer");

        $("#maidName").val(maidName);
        $("#contractRef").val(contractRef);
        $("#customerComp").val(customer);
        $("#reasonInput").val("");

        $("#signup-modal").modal("show");

        selectServerSideSearch(
            "#assignedToSelect",
            "/searching-user",
            "#comp_modal",
            "search staff"
        );

    });
});

$("#p4dataTable").on("click", ".delete-sign", function () {
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
            fetch(`/delete_sign/p4/${id}`, {
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
                        $("#p4dataTable").DataTable().ajax.reload();
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
