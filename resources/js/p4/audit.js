import { display_table } from "../reuseable/display_table";

$(document).ready(function () {
    const columns = [
        { data: "date", name: "date" },
        { data: "Contract_ref" },
        { data: "phone" },
        { data: "customer" },
        { data: "maid" },
        { data: "maid_type" },
        { data: "maid_payment" },
        { data: "maid_salary" ,searchable: false },
        { data: "customer_invoice" },
        { data: "amount_invoice" },
        { data: "installment" },
        { data: "customer_invoice_note" , title: "Customer Invoice Note", searchable: false },
        { data: "user_type" },
        { data: "created_by" },
    ];

    display_table("/p4-audit", "#p4dataTable", columns);

    $('#installmentZero').change(function () {
        let zero = $(this).is(':checked') ? 1 : ''; 
        let url = '/p4-audit' + (zero ? '?installmentZero=' + zero : ''); 
    
        $('#p4dataTable').DataTable().ajax.url(url).load();
    });
    

    function applyFilters() {
        let filterContract = $("#filterContracts").val();
        let filterDep = $("#filterDep").val();
        let filterNew = $("#filterNew").val();

        $("#p4dataTable")
            .DataTable()
            .column(5)
            .search(filterContract)
            .draw()
            .column(12)
            .search(filterDep)
            .draw()
            .column(1)
            .search(filterNew)
            .draw();
    }

    $("#filterContracts ,#filterDep,#filterNew").on("change", applyFilters);
});
