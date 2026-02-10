
import { display_table } from "../reuseable/display_table";
import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { rvAndCrNoteForMaids } from "../reuseable/rvAndCrNoteMaids";


    $(document).ready(function() {

        handleFormPostSubmission('paymentForm','/maids-payment','#invoice-table','#payment-modal')
        handleFormPostSubmission('creditNoteForm','/credit-note-maidssales','#invoice-table','#credit-note-modal')

        const columns = [
            { 
                data: 'id',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `<input type="checkbox" class="row-checkbox" value="${data}">`;
                }
            },
            { data: 'date' },         
            { data: 'refCode' },          
            { data: 'contract_ref'},         
            { data: 'account' },             
            { data: 'maid_name' },           
            { data: 'pre_connection_name' },  
            { data: 'amount' },              
            { data: 'invoice_balance' },      
            { data: 'notes' },               
            { data: 'payment_status', searchable: false },  
            { data: 'receiveRef' },         
            { data: 'creditNoteRef' },      
            { data: 'action', orderable: false, searchable: false }  
        ];
        

           display_table('/ajax-invoices-cat4','#invoice-table',columns)

              const $status = document.getElementById("invoice-balance");

            $status.addEventListener("change", () => {
                const dt = $("#invoice-table").DataTable();
                dt.ajax.url(`/ajax-invoices-cat4?invoice_balance=${$status.value}`).load();
            });
    });
 

    
$(document).on('click', '.btn-apply-credit', function(e){
    e.preventDefault();
    let apply_credit_id = $(this).data('payment');

    $.ajax({
        type: 'POST',
        url: '/apply-credit',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
        },
        data: {
            transactionID: apply_credit_id
        },
        success: function(response) {
            if (response.status === 'success') {
                alert(response.message);
                $('#invoice-table').DataTable().ajax.reload(null, false);
       
            }
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            alert(xhr.responseText);
        }
    });
});


$(document).on('click', '#apply-credit-bulk', function () {
    // Collect selected IDs
    const ids = [];
    $('.row-checkbox:checked').each(function () {
        ids.push($(this).val());
    });

    if (ids.length === 0) {
        alert('Please select at least one invoice.');
        return;
    }

    $.ajax({
        url: '/apply-credit-bulk',
        type: 'POST',
        data: {
            ids: ids,
            _token: $('meta[name="csrf-token"]').attr('content') 
        },
        success: function (res) {
            alert(`Updated: ${res.updated} / ${res.requested}`);
            $('#invoice-table').DataTable().ajax.reload(null, false);
        },
        error: function (xhr) {
            alert('Error applying credit: ' + xhr.responseJSON?.message ?? 'Unknown error');
        }
    });
});


// Toggle all rows when header checkbox is clicked
$(document).on('change', '#check-all', function () {
    const isChecked = $(this).is(':checked');
    $('.row-checkbox').prop('checked', isChecked);
});

// If any row checkbox changes, update header checkbox state
$(document).on('change', '.row-checkbox', function () {
    const total = $('.row-checkbox').length;
    const checked = $('.row-checkbox:checked').length;
    $('#check-all').prop('checked', total === checked);
});

 rvAndCrNoteForMaids