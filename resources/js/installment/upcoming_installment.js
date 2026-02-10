import { display_table } from "../reuseable/display_table";
import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";

const checkTotal = () => {
    const salary = Number(document.getElementById("maid_salary")?.value) ?? 0;
    const netProfit = Number(document.getElementById("net_profit")?.value) ?? 0;
    const totalInvoice =
        Number(document.getElementById("total_amount")?.value) ?? 0;

    const sumNetSalary = salary + netProfit;
    const submitButton = document.getElementById("btn-submit");
    submitButton.disabled = sumNetSalary !== totalInvoice;
};

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("#maid_salary, #net_profit").forEach((input) => {
        input.addEventListener("input", checkTotal);
    });

    handleFormPostSubmission(
        "customized-form",
        "/store/customize",
        "#table_installment",
        "#custom-modal"
    );
    display_table("/table/installment", "#table_installment", [
        { data: 'select',  orderable:false, searchable:false }, 
        { data: "accrued_date" },
        { data: "customer" },
        { data: "phone" },
        { data: "maid_name",  },
        { data: "maid_type"  },
        { data: "note" },
        { data: "cheque" },
        { data: "contract" },
        { data: "amount" },
        { data: "created_by" },
        { data: "meta" },
        { data: "custom" },
    ]);


        function applyFilters() {
        let firstFilter = $("#filterContractType").val();

        $("#table_installment")
            .DataTable()
            .column(5)
            .search(firstFilter)
            .draw();
    }
    

    $("#filterContractType").on("change", applyFilters);


    $(document).on("click", ".customized-invoice", function () {
        let id = $(this).data("id");

        $.ajax({
            url: "/installment/" + id,
            type: "GET",
            success: function (data) {
                const net_amount =
                    data.amount - data.contract_ref.maid_info?.salary;

                $("#custom-modal").modal("show");
                $("#id_installment").val(data.id);

                $("#net_profit").val(net_amount);
                $("#contract_ref").val(data.contract);
                $("#maid_name").val(data.contract_ref.maid_info?.name);
                $("#total_amount").val(data.amount);
                $("#cheque").val(data.cheque);
                $("#note").val(data.note);
                $("#date_installment").val(data.accrued_date);
                $("#customer_installment").val(data.customer_info?.name);
                $("#maid_salary").val(data.contract_ref.maid_info?.salary);
            },
            error: function (xhr) {
                alert("Error: " + xhr.responseJSON.message);
            },
        });
    });

    $("#table_installment").on("click", ".generate-invoice", function () {
        const amount = $(this).data("amount");
        const salary = $(this).data("salary");
        const customer = $(this).data("customer");
        const date = $(this).data("date");
        const maid = $(this).data("maid");
        const contract = $(this).data("contract");
        const cheque = $(this).data("cheque");
        const note = $(this).data("note");
        const id = $(this).data("id");

        Swal.fire({
            title: "Are you sure?",
            text: `You are about to generate an invoice for customer: ${customer}. Proceed? 
            
             note: ${note} cheque: ${cheque} contract: ${contract} maid: ${maid} date: ${date} customer: ${customer} salary: ${salary} amount: ${amount}`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, generate it!",
            cancelButtonText: "No, cancel!",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/installment/store",
                    type: "POST",
                    data: {
                        amount: amount,
                        salary: salary,
                        customer: customer,
                        date: date,
                        maid: maid,
                        contract: contract,
                        cheque: cheque,
                        note: note,
                        id: id,
                        _token: $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function (response) {
                        Swal.fire(
                            "Success!",
                            "Invoice generated successfully.",
                            "success"
                        );

                        $("#table_installment")
                            .DataTable()
                            .ajax.reload(null, false);
                    },
                    error: function (jqXHR) {
                        let errorMessage =
                            jqXHR.responseJSON.message ||
                            "There was a problem generating the invoice. Please try again.";

                        Swal.fire("Error!", errorMessage, "error");
                    },
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire(
                    "Cancelled",
                    "Invoice generation was cancelled.",
                    "info"
                );
            }
        });
    });



let selectedIds = new Set();

/* -------------------------------------------------
   2.  Keep checkbox state on every table redraw
---------------------------------------------------*/
$('#table_installment').on('draw.dt', function () {

    // (re)-bind header “select-all”
    $('#select-all')
        .off('click')
        .on('click', function () {
            const checked = this.checked;
            $('#table_installment .row-select')
                .prop('checked', checked)
                .trigger('change');           
        });

    // restore row-checkbox states from the Set
    $('#table_installment .row-select').each(function () {
        this.checked = selectedIds.has(this.value);
    });

    // sync header checkbox
    const rows   = $('#table_installment .row-select').length;
    const checked= $('#table_installment .row-select:checked').length;
    $('#select-all').prop('checked', rows && rows === checked);
});

/* -------------------------------------------------
   3.  Track individual row (de)selection
---------------------------------------------------*/
$('#table_installment').on('change', '.row-select', function () {

       if (this.checked && selectedIds.size >= 50) {
        this.checked = false;                               // revert UI
        return Swal.fire(
            'Limit reached',
            'You can bulk-generate up to 50 invoices at a time.',
            'info'
        );
    }



    if (this.checked) {
        selectedIds.add(this.value);
    } else {
        selectedIds.delete(this.value);
    }

    // keep header in sync within the current page
    const rows   = $('#table_installment .row-select').length;
    const checked= $('#table_installment .row-select:checked').length;
    $('#select-all').prop('checked', rows && rows === checked);
});

/* -------------------------------------------------
   4.  Bulk “Generate selected invoices” button
---------------------------------------------------*/
$('#bulk-generate').on('click', function () {

    if (selectedIds.size === 0) {
        return Swal.fire('Nothing selected', 'Tick at least one row first.', 'info');
    }



    Swal.fire({
        title: `Generate invoices for ${selectedIds.size} installment(s)?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, generate',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: '/installment/bulk-store',
            type: 'POST',
            data: {
                ids: Array.from(selectedIds),
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (resp) {
                Swal.fire('Success', resp.message, 'success');
                selectedIds.clear();                   
              $("#table_installment")
                            .DataTable()
                            .ajax.reload(null, false);     
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Bulk generation failed.';
                Swal.fire('Error', msg, 'error');
            },
        });
    });
});
});
