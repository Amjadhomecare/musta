import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { display_table } from "../reuseable/display_table";
import { selectServerSideSearch } from "../reuseable/server_side_search";

document.addEventListener("DOMContentLoaded", () => {



    display_table("/stripe", "#stripe_payment", [
        { data: "id", title: "ID" },
        { data: "amount", title: "Amount" },
        { data: "currency", title: "Currency" },
        { data: "description", title: "Description" },
        { data: "status", title: "Status" },
        { data: "product_name", title: "stripe_maid" },
        { data: "created", title: "Created At", orderable: true },
        { data: "billing_email", title: "Billing Email" },
        { data: "billing_name", title: "Billing Name" },
    ]);

    display_table("/async-stripepay", "#async_stripe_payment", [
        { data: "amount", title: "Amount" },
        { data: "description", title: "Description" },
        { data: "status", title: "Status" },       
        { data: "refunded", title: "refunded" },
        { data: "refunded_amount", title: "refunded_amount" },
        { data: "receipt_url", title: "Receipt" },
        { data: "stripe_created_at", title: "Created At", orderable: true },
        { data: "billing_email", title: "Billing Email" },
        { data: "billing_name", title: "Billing Name" },
        { data: "customer_erp", title: "Erp customer" },
        { data: "maid_erp", title: "Erp maid" },
        { data: "sub_id", title: "sub_id" },
        { data: "sub_start", title: "Subscription start" },
        { data: "sub_status", title: "Sub_status" },


        {
            data: "action",
            title: "Actions",
            orderable: false,
            searchable: false,
        },
        {
            data: "stripe_id",
            render: function (data, type, row) {
                return `<a href="https://dashboard.stripe.com/payments/${row.stripe_id}" target='_blank' >${row.stripe_id}</a>`;
            },
        }

        
    ]);

    $('#partial_refunded').change(function () {
        let partial_refunded = $(this).is(':checked') ? 'true' : 'false';
    
        $('#async_stripe_payment')
            .DataTable()
            .ajax.url('/async-stripepay?partial_refund=' + partial_refunded)
            .load();
    });
    
    
function applyFilters() {
    let payment = $("#status_pay").val();  
    let statusFilter = $("#statusFilter").val();  

    $("#async_stripe_payment")
        .DataTable()
        .column(2)
        .search(payment)
        .column(3) 
        .search(statusFilter)
        .draw();
}

$("#statusFilter,#status_pay").on("change", applyFilters);

$(document).on("click", ".pay-stripe-btn", function () {
        let stripeID = $(this).data("id");

        $.ajax({
            url: "/fetch-charges/" + stripeID,
            type: "GET",
            success: function (data) {
                $("#stripe-id").val(data.stripe_id);
                $("#stripe-amount").val(data.amount);
                $("#stripe-status").val(data.status);
                $("#billing-name").val(data.billing_name);
                $("#stripe-created-at").val(data.stripe_created_at);

                $("#pay-stripe-modal").modal("show");

                selectServerSideSearch(
                    "#customerSelect",
                    "/add/new/ledger",
                    "#pay-stripe-modal",
                    "Search customer"
                );

                selectServerSideSearch(
                    "#maidSelect",
                    "/all/maids",
                    "#pay-stripe-modal",
                    "Search Maid"
                );
            },
            error: function (xhr) {
                alert("Error: " + xhr.responseJSON.message);
            },
        });
    });

    handleFormPostSubmission(
        "stripe-details-form",
        "/stripe/erp-pay",
        "#async_stripe_payment",
        "#pay-stripe-modal"
    );
});



document.getElementById('sync-charges').addEventListener('click', () => {
    const button = document.getElementById('sync-charges');
    if (confirm('Are you sure you want to sync charges?')) {
   
        button.disabled = true;
        const originalText = button.innerHTML;
        button.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Syncing...`;

        fetch("/stripe/sync-charges", {
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
                $('#async_stripe_payment').DataTable().ajax.reload();
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