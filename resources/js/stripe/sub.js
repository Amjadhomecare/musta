import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { display_table } from "../reuseable/display_table";
import { selectServerSideSearch } from "../reuseable/server_side_search";

document.addEventListener("DOMContentLoaded", () => {
    display_table("/table/stripe-sub", "#stripe_sub", [
        {
            data: "sub_id",
            render: function (data, type, row) {
                return `<a href="https://dashboard.stripe.com/subscriptions/${row.sub_id}" target='_blank' >${row.sub_id}</a>`;
            },
        },
        { data:"created_date"},
        {
            data: "customer_erp",
            render: function (data, type, row) {
                return `<a href="/customer/report/p4/${row.customer_erp}" target='_blank' >${row.customer_erp}</a>`;
            },
        },
        { data:"status"},
        { data:"cancelled_at"},
        { data:"monthly_amount"},
         {data:"action"}
      
    ]);


    function applyFilters() {

        let statusFilter = $("#statusFilter").val();  
        $("#stripe_sub")
            .DataTable()
            .column(3) 
            .search(statusFilter)
            .draw();
    }
    
    $("#statusFilter").on("change", applyFilters);

    handleFormPostSubmission('stripe-details-form' , '/sub-update' ,'#fff',"#sub-stripe-modal")
   
    $(document).on("click", ".sub-stripe-btn", function () {
        let stripeID = $(this).data("id");

        $.ajax({
            url: "/fetch-sub/" + stripeID,
            type: "GET",


            success: function (data) {
                   
                console.log(data);
                
                $("#stripe_sub_id").val(data.id)
                $("#stripe-amount").val(data.plan.amount/100)
                $("#sub-stripe-modal").modal("show");


                selectServerSideSearch(
                    "#customerSelect",
                    "/add/new/ledger",
                    "#sub-stripe-modal",
                    "Search customer"
                );

                selectServerSideSearch(
                    "#maidSelect",
                    "/all/maids",
                    "#sub-stripe-modal",
                    "Search Maid"
                );
            },
            error: function (xhr) {
                alert("Error: " + xhr.responseJSON.message);
            },
        });
    });


document.getElementById('sync-charges').addEventListener('click', () => {
    const button = document.getElementById('sync-charges');
    if (confirm('Are you sure you want to sync charges?')) {
   
        button.disabled = true;
        const originalText = button.innerHTML;
        button.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Syncing...`;

        fetch("/async-sub", {
            method: 'POST',
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
        })
        .then(response => response.json())
        .then(data => {
           
            button.disabled = false;
            button.innerHTML = originalText;

            if (data.error) {
                alert('Error: ' + data.error);
            } else {
                alert(data.message);
                $('#stripe_sub').DataTable().ajax.reload();
            }
        })
        .catch(error => {
  
            button.disabled = false;
            button.innerHTML = originalText;

            console.error('Error:', error);
            alert('An error occurred while syncing charges.');
        });
    }
});


});
