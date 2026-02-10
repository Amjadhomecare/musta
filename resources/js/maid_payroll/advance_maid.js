import { display_table } from "../reuseable/display_table";
import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";


$(document).ready(function () {
    display_table("/data-advance", "#advance_datatable", [
        { data: "date" },
        { data: "maid" },
        { data: "note" },
        { data: "deduction" },
        { data: "Allowance" },
        { data: "created_at" },
        { data: "updated_at" },
        { data: "created_by" },
        { data: "updated_by" },
    ]);

    
    const maidName = document.getElementById("maidSelectPayroll").value;



    display_table(`/dedction-maid/${maidName}`,"#advance_maid_datatable", [
        { data: "date" },
        { data: "maid" , searchable: false },
        { data: "note" },
        { data: "deduction" },
        { data: "Allowance" },
        { data: "created_at" },
        { data: "updated_at" },
        { data: "created_by" },
        { data: "updated_by" },
        {
            data: "id",
            render: function (data, type, row) {
                return `
                    <button class="btn btn-primary open-modal-btn" 
                        data-deduction="${row.deduction}" 
                        data-allowance="${row.Allowance}" 
                        data-note="${row.note}" 
                        data-id="${data}" 
                        data-maid_name="${row.maid}">
                        Edit
                    </button>`;
            },
            orderable: false,
            searchable: false,
        },
    ]);

        
    handleFormPostSubmission(
        "maidDeductionForm",
        "/update-advance",
        "#advance_maid_datatable",
        "#maid-dedction"
    );
 


});


$(document).on("click", ".open-modal-btn", function () {
    let deduction = $(this).data("deduction");
    let allowance = $(this).data("allowance");
    let note = $(this).data("note");
    let id = $(this).data("id");
    let maidName = $(this).data("maid_name");

    $("#maidNameForDeduction").val(maidName);
    $("#deductionInput").val(deduction);
    $("#allowanceInput").val(allowance);
    $("#noteInput").val(note);
    $("#idForDeduction").val(id);

    $("#maid-dedction").modal("show");
});
