import { display_table } from "../reuseable/display_table";

document.addEventListener("DOMContentLoaded", () => {
    display_table(`/ajax/all/paid-payroll`, "#payroll-maids", [
        { data: "accrued_month" },
        { data: "maid" },
        { data: "basic" },
        { data: "maid_type" },
        { data: "maid_moi" },
        { data: "maid_branch" },        
        { data: "working_dayes" },
        { data: "status" },
        { data: "method" },
        { data: "deduction" },
        { data: "allowance" },
        { data: "note" },
        { data: "net_salary" },
        { data: "created_by" },
        { data: "created_at" },
        { data: "delete" },
    ]);

    function applyFilters() {
        let paidFilter = $("#paymentWay").val();

        $("#payroll-maids").DataTable().column(8).search(paidFilter).draw();
    }

    $("#paymentWay").on("change", applyFilters);

  // Delete handler

  document.querySelector("#payroll-maids").addEventListener("click", (e) => {
    if (e.target.classList.contains("delete-btn")) {
        const id = e.target.getAttribute("data-id");

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
                fetch(`/delete-payroll/${id}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    },
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error("Failed to delete the record");
                        }
                        return response.json();
                    })
                    .then((data) => {
                        if (data.message) {
                            Swal.fire("Deleted!", data.message, "success");
                            $("#payroll-maids")
                                        .DataTable()
                                        .ajax.reload();
                        } else {
                            Swal.fire("Error!", "An unexpected error occurred.", "error");
                        }
                    })
                    .catch((error) => {
                        Swal.fire("Error!", error.message || "An error occurred.", "error");
                        console.error(error);
                    });
            }
        });
    }
});

});


