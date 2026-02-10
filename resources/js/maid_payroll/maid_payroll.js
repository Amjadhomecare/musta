import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";

const urlParams = new URLSearchParams(window.location.search);
const month = urlParams.get("month");
const year = urlParams.get("year");

const formattedMonth = month.padStart(2, "0");
const dateString = `${year}-${formattedMonth}-'25'`;
const selectedDate = new Date(dateString);

const formattedDate =
    selectedDate.getFullYear() +
    "-" +
    String(selectedDate.getMonth() + 1).padStart(2, "0") +
    "-" +
    String(selectedDate.getDate()).padStart(2, "0");

document.addEventListener("DOMContentLoaded", () => {
    let dataTable = $("#maidsDataTable").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "/maids/payrolls",
            type: "GET",
            data: function (d) {
                d.month = month;
                d.year = year;
            },
        },

        columns: [
            {
                data: null,
                defaultContent: "",
                render: function (data, type, row) {
                    return `<input type="checkbox" class="maid-checkbox" value="${
                        row.id
                    }" 
                                  data-maid="${row.name}" 
                                  data-totaldays="${row.totalDays}" 
                                  data-deduction="${row.deduction}" 
                                  data-allowance="${row.allowance}" 
                                  data-note="${row.note}"
                                  data-salary="${row.salary}"
                                  data-net= "${
                                      Math.round(row.salary / 30) *
                                          row.totalDays -
                                      row.deduction +
                                      row.allowance
                                  }" 
                                  data-type="${row.type}"
                                  data-method="${row.payment}"
                                  data-status="${row.maid_status}"
    
                                  >`;
                },
                orderable: false,
            },

            {
                data: "name",
                render: function (data, type, row) {
                    return `<a href="/maid-report/p4/${row.name}" target='_blank' >${row.name}</a>`;
                },
            },
            { data: "salary", name: "salary" },
            { data: "type", name: "type" },
            { data: "totalDays", name: "totalDays" },
            { data: "maid_status", name: "maid_status" },
         
            { data: "is_paid", name: "is_paid" },
            { data: "payment", name: "payment" },
            { data: "contract_ref", name: "contract_ref" },
            { data: "book", name: "book" },

            {
                data: "name",
                render: function (data, type, row) {
                    return `<a href="/customer/report/p4/${row.customer}" target='_blank' >${row.customer}</a>`;
                },
            },
            { data: "deduction", name: "deduction" },
            { data: "allowance", name: "allowance" },
            { data: "note", name: "note" },
            {
                data: "id",
                render: function (data, type, row) {
                    return `${
                        Math.round(row.salary / 30) * row.totalDays -
                        row.deduction +
                        row.allowance
                    }  `;
                },
            },

            {
                data: "id",
                render: function (data, type, row) {
                    if (row.idForDeduction == "") {
                        return "<p> No data </p>";
                    } else {
                        return `<button type="button" class="btn btn-primary btn-sm open-modal-btn" data-bs-toggle="modal" data-bs-target="#signup-modal" data-deduction="${row.deduction}" data-allowance="${row.allowance}" data-note="${row.note}" data-id="${row.idForDeduction}">Edit</button>`;
                    }
                },
            },
        ],
        createdRow: function (row, data, dataIndex) {
            if (data.paidMaid === true) {
                $("#maidsDataTable").DataTable().row(row).remove().draw();
            }
        },
        dom: "Blfrtip",
        buttons: ["excel"],
        pagingType: "full_numbers",
        pageLength: 10,
        lengthMenu: [
            [10, 50, 100, 300, 600, -1],
            [10, 50, 100, 300, 600, "All"],
        ],

     
    });

    // Debounce function
    function debounce(func, delay) {
        let debounceTimer;
        return function () {
            const context = this;
            const args = arguments;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => func.apply(context, args), delay);
        };

    }

    $(document).ready(function () {
        let dataTable = $('#maidsDataTable').DataTable(); 
    
        function applyFilters() {
            let filterNoRemark = $("#filterCheckboxNoRemark").is(":checked");
            let filterRemark = $("#filterCheckboxRemark").is(":checked");
            let filterBooked = $("#filterCheckboxBooked").is(":checked");
            dataTable.column(13).search("");
            dataTable.column(9).search("");
    
            if (filterNoRemark) {
                dataTable.column(13).search("^$", true, false);
                dataTable.column(9).search("^$", true, false);
            }
    
            if (filterRemark) {
                dataTable.column(13).search("^(?!$)", true, false);
            }
    
            if (filterBooked) {
                dataTable.column(9).search("^(?!$)", true, false);
            }
    
          
            dataTable.draw();
        }
    
        // Attach event listener to checkboxes
        $("#filterCheckboxNoRemark, #filterCheckboxRemark, #filterCheckboxBooked").on("change", function () {
            applyFilters();
        });
    });
    
    

    // Apply debounced search on the DataTable's search bar
    $(`#maidsDataTable_filter input`)
        .unbind() // Unbind any default event handler
        .bind(
            "keyup",
            debounce(function () {
                dataTable.search(this.value).draw();
            }, 700) // 700ms debounce delay
        );

    handleFormPostSubmission(
        "maidDeductionForm",
        "/update-advance",
        "#maidsDataTable",
        "#maid-dedction"
    );

    $("#maidStatus").on("change", function () {
        dataTable.column(5).search(this.value).draw();
    });

    $("#maidType").on("change", function () {
        dataTable.column(3).search(this.value).draw();
    });

    $("#paymentWay").on("change", function () {
        dataTable.column(7).search(this.value).draw();
    });

    $("#paidStatus").on("change", function () {
        const paidStatus = this.value;

        if (paidStatus) {
            dataTable
                .column(6)
                .search("^" + paidStatus + "$", true, false)
                .draw();
        } else {
            dataTable.column(6).search("").draw();
        }
    });

    $("#days").on("change", function () {
        let selectedDays = this.value;
        dataTable
            .column("totalDays:name")
            .search(
                (function () {
                    if (selectedDays === "lessThan28") {
                        return "^(1[0-9]|2[0-7]|[1-9])$";
                    } else if (selectedDays === "moreThanEqual28") {
                        return "^(2[89]|[3-9][0-9])$";
                    } else {
                        return "";
                    }
                })(),
                true,
                false
            )
            .draw();
    });

    $(document).ready(function () {
        $(document).on("click", ".open-modal-btn", function () {
            let deduction = $(this).data("deduction");
            let allowance = $(this).data("allowance");
            let note = $(this).data("note");
            let id = $(this).data("id");

            $("#deductionInput").val(deduction);
            $("#allowanceInput").val(allowance);
            $("#noteInput").val(note);
            $("#idForDeduction").val(id);

            $("#maid-dedction").modal("show");
        });
    }); // end Open model

    $(document).ready(function () {
        $("#bulkSaveButton").click(function () {
            let selectedMaids = $(".maid-checkbox:checked")
                .map(function () {
                    let row = $(this).closest("tr");
                    return {
                        id: this.value,
                        date: formattedDate,
                        name: $(this).data("maid"),
                        type: $(this).data("type"),
                        totalDays: $(this).data("totaldays"),
                        deduction: $(this).data("deduction"),
                        allowance: $(this).data("allowance"),
                        note: $(this).data("note"),
                        salary: $(this).data("salary"),
                        net: $(this).data("net"),
                        method: $(this).data("method"),
                        status: $(this).data("status"),
                    };
                })
                .get();

            if (selectedMaids.length > 0) {
                $.ajax({
                    url: "/bulk-paid",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({ maids: selectedMaids }),
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    success: function (response) {
                        alert(response.message);
                        dataTable.ajax.reload();
                    },
                    error: function (xhr, status, error) {
                        alert("Error: " + error.message);
                    },
                });
            } else {
                alert("No maids selected for bulk payment.");
            }
        });
    });
}); //End Method


$(document).ready(function () {
    $("#selectAllMaids").on("click", function () {
        let isChecked = $(this).is(":checked");
        $(".maid-checkbox").prop("checked", isChecked);
    });
    $(document).on("click", ".maid-checkbox", function () {
        let totalCheckboxes = $(".maid-checkbox").length;
        let checkedCheckboxes = $(".maid-checkbox:checked").length;

        $("#selectAllMaids").prop(
            "checked",
            totalCheckboxes === checkedCheckboxes
        );
    });
});
