import { ajaxSelector } from "../ajax_select2";
import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { display_table } from "../reuseable/display_table";

if ($.fn.DataTable.isDataTable("#all_typing")) {
    $("#all_typing").DataTable().destroy();
}
document.addEventListener("DOMContentLoaded", () => {
    const columns = [
        { 
                data: 'id',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `<input type="checkbox" class="row-checkbox" value="${data}">`;
                }
        },
        { data: "date" },
        { data: "refCode" },
        { data: "account" },
        { data: "pre_connection_name" },
        { data: "amount" },
        { data: "invoice_balance" },
        { data: "payment_status", searchable: false },
        { data: "notes" },
        { data: "receiveRef" },
        { data: "creditNoteRef" },
        { data: "created_by" },
        { data: "action", orderable: false, searchable: false },
    ];
    display_table("/typing-invoices", "#all_typing", columns);


 const $status = document.getElementById("invoice-balance");

  $status.addEventListener("change", () => {
    const dt = $("#all_typing").DataTable();
    dt.ajax.url(`/typing-invoices?invoice_balance=${$status.value}`).load();
  });
});




handleFormPostSubmission(
    "paymentForm",
    "/receive-payment",
    "#all_typing",
    "#typing-payment-modal"
);

handleFormPostSubmission(
    "creditNoteForm",
    "/store-credit-note-data",
    "#all_typing",
    "#typing-credit-note-modal"
);

$(document).on("click", ".btn-apply-credit", function (e) {
    e.preventDefault();
    let apply_credit_id = $(this).data("payment");

    $.ajax({
        type: "POST",
        url: "/apply-credit",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            Accept: "application/json",
        },
        data: {
            transactionID: apply_credit_id,
        },
        success: function (response) {
            if (response.status === "success") {
                alert(response.message);
                $("#all_typing").DataTable().ajax.reload(null, false);
            }
        },
        error: function (xhr) {
            console.log(xhr.responseText);
            alert(xhr.responseText);
        },
    });
});

document.addEventListener("DOMContentLoaded", () => {
    if ($("#selected_customer").length) {
        ajaxSelector(
            "#selected_customer",
            "/all-customers",
            "Search for a customer",
            "#add_transactions .modal-body"
        );
    } else {
        console.log("Select2 initialization skipped: Element missing.");
    }

    $("#add_transactions").on("shown.bs.modal", function () {
        let selectElement = $("#connectionSelect");
    
        selectElement.select2({
            ajax: {
                url: "/list-invoice-preconnection",
                dataType: "json",
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term,
                        page: params.page || 1,
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.items,
                        pagination: {
                            more: params.page * 30 < data.total_count,
                        },
                    };
                },
                cache: true,
            },
            placeholder: "Search for a service",
            minimumInputLength: 1,
            allowClear: true,
            dropdownParent: $("#add_transactions"),
        });
    

        setTimeout(() => {
            selectElement.select2("open");
        }, 300);
    

        selectElement.on("select2:open", function () {
            setTimeout(() => {
                let searchField = document.querySelector(".select2-container--open .select2-search__field");
                if (searchField) {
                    searchField.focus();
                }
            }, 400);
        });
    });
    

    // Handle item selection
    $("#connectionSelect").on("select2:select", function (e) {
        let data = e.params.data.full_data;
        addAccountEntry(data, 0);
    });

    function addAccountEntry(connectionData = null, index) {
        let container = document.getElementById("entryContainer");
        let entryRow = document.createElement("div");
        entryRow.className = "row mb-3 align-items-end";
        const uniqueId = Date.now() + index;
        entryRow.dataset.uniqueId = uniqueId;

        entryRow.innerHTML = `

            <div class="col">
            <label class="form-label">Ref:</label>
            <input type="text" name="notes[]" class="form-control">
        </div>

        <div class="col">
            <label class="form-label">Service Name:</label>
            <select name="typing_services[]"  class="form-control">
                <option value="${connectionData.invoice_connection_name}">${
            connectionData.invoice_connection_name
        }</option>
            </select>
        </div>

        <div class="col">
            <label class="form-label">Amount:</label>
            <input  readOnly type="number" name="amount" id="amount${uniqueId}" class="form-control"  value="${
            connectionData ? connectionData.total_credit : ""
        }">
        </div>

  
        <div class="col">
            <label class="form-label">QTN:</label>
            <input type="number" value="1" id="qty${uniqueId}" name="qtn[]" class="form-control">
        </div>

        <div class="col-auto">
            <button type="button" class="btn btn-danger removeAccountEntry">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>
        `;

        container.appendChild(entryRow);

        updateTotals();
    }

    $(document).on("click", ".removeAccountEntry", function () {
        let entryRow = $(this).closest(".row");
        entryRow.remove();
        updateTotals();
    });

    $(document).on(
        "change",
        'input[name="amount"], input[name="qtn[]"]',
        function () {
            updateTotals();
        }
    );

    function updateTotals() {
        let totalAmount = 0;

        document.querySelectorAll("div.row").forEach((row) => {
            const qtyInput = row.querySelector('input[name="qtn[]"]');
            const amountInput = row.querySelector('input[name="amount"]');

            const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
            const amount = parseFloat(amountInput ? amountInput.value : 0) || 0;

            totalAmount += qty * amount;
        });

        // Update the total field
        let total = document.getElementById("totalCredit");
        total.value = totalAmount.toFixed(2);
    }

    $(document).on("click", ".open-modal-btn", function () {
        let customerName = $(this).data("customer");
        let idRef = $(this).data("id");
        let invoiceRef = $(this).data("invoice");
        let note = $(this).data("note");

        $("#customerNameInput").val(customerName);
        $("#idInput").val(idRef);
        $("#invRef").val(invoiceRef);
        $("#noteInput").val(note);

        $("#typing-payment-modal").modal("show");
    });

    $(document).on("click", "#add-transaction", function (e) {
        e.preventDefault();

        // Disable the button and change the text to "Submitting..."
        $("#add-transaction").prop("disabled", true).val("Submitting...");

        let formData = $("#transactionsForm").serialize();

        $.ajax({
            type: "POST",
            url: "/add/invoice/typing/testing",
            data: formData,
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                Accept: "application/json",
            },
            success: function (data) {
                if (data.success) {
                    // Handle success response
                    $("#add-transactions").html("Submitting");

                    $("#add_transactions").modal("hide");

                    $("#transactionsForm")[0].reset();
                    $("#entryContainer").empty();
                    $("#selected_customer").empty();
                    $("#connectionSelect").empty();

                    $("#all_typing").DataTable().ajax.reload(null, false);

                    toastr.success(data.message);

                    // Re-enable the button and set the text back to "Submit"
                    $("#add-transaction").prop("disabled", false).val("Submit");
                } else {
                    toastr.options = {
                        positionClass: "toast-top-full-width",
                    };
                    toastr.error(data.message);

                    // Re-enable the button and set the text back to "Submit"
                    $("#add-transaction").prop("disabled", false).val("Submit");
                }
            },
            error: function (xhr, status, error) {
                // Handle the error response and show an appropriate message
                toastr.error(
                    "An error occurred while submitting the form. Please try again."
                );

                // Re-enable the button and set the text back to "Submit"
                $("#add-transaction").prop("disabled", false).val("Submit");
            },
            complete: function () {
                // This will run after both success or error to make sure the button is always re-enabled
                $("#add-transaction").prop("disabled", false).val("Submit");
            },
        });
    });

    function formatCreditNoteDataToHtml(data) {
        let htmlContent = "";

        data.forEach(function (item) {
            htmlContent += `
            <div class="credit-note-item row">
                <div class="col-md-3">
                    <input name="account[]" class="form-control" readOnly value="${item.account_ledger.ledger}" >
                </div>
                <div class="col-md-3">
                    <input name="accountType[]" class="form-control" readOnly value="${item.type}">
                </div>
                <div class="col-md-3">
                    <input name="accountAmount[]" class="form-control" value="${item.amount}">
                </div>
                <input type="hidden" name="refCode[]" id="refCode">
            </div>
            <hr>`;
        });

        return htmlContent;
    }

    $(document).on("click", ".btn-credit-note", function () {
        let refCode = $(this).attr("data-refCode");
        let refId = $(this).attr("data-idForCustomer");

        if (refCode) {
            $.ajax({
                url: "/get-credit-note-data",
                type: "POST",
                data: {
                    refCode: refCode,
                },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                success: function (response) {
                    let formattedHtml = formatCreditNoteDataToHtml(response);
                    $("#creditNoteModalContent").html(formattedHtml);
                    $("#refCode").val(refId);
                },
                error: function (xhr) {
                    console.error("Error: ", xhr.statusText);
                },
            });
        } else {
            console.error("No refCode found for the AJAX request.");
        }
    });
});


document.addEventListener("keydown", function(event) {
      
    if (event.key.toLowerCase() === "m" && !event.target.matches("input, textarea, select")) {
        event.preventDefault(); 
        document.querySelector("#btn_id").click(); 
    }
});


document.addEventListener("DOMContentLoaded", function() {
    let modal = document.getElementById("add_transactions");

    modal.addEventListener("shown.bs.modal", function () {
        document.getElementById("selected_customer").focus();
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
            $('#all_typing').DataTable().ajax.reload(null, false);
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