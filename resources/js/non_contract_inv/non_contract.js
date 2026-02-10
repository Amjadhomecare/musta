import { display_table, updateTotals } from "../reuseable/display_table";
import {
    initializeModalSelect2,
    initializeSelect2,
} from "../reuseable/slelect2_for_modal";
import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";

const row_data = ["id", "maid_name", "account"];

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
    { data: "maid_name" },
    { data: "pre_connection_name" },
    { data: "amount" },
    { data: "invoice_balance" },
    { data: "notes" },
    { data: "payment_status", searchable: false },
    { data: "receiveRef" },
    { data: "creditNoteRef" },
    { data: "created_by" },
    { data: "created_at" },
    { data: "action", orderable: false, searchable: false },
];

display_table("/ajax/list/invoices", "#non_contract", columns);


       const $status = document.getElementById("invoice-balance");

            $status.addEventListener("change", () => {
                const dt = $("#non_contract").DataTable();
                dt.ajax.url(`/ajax/list/invoices?invoice_balance=${$status.value}`).load();
            });

document.addEventListener("DOMContentLoaded", () => {
    initializeSelect2(
        "#selected_maid",
        "/all/maids",
        "Search for a maid",
        "#non_contract_add_transactions .modal-body"
    );
    initializeSelect2(
        "#selected_customer",
        "/all-customers",
        "Search for a customer",
        "#non_contract_add_transactions .modal-body"
    );
    initializeModalSelect2(
        "#non_contract_add_transactions",
        "#connectionSelect",
        "/list-invoice-preconnection-non-contract",
        "Search for a service"
    );

    document
        .querySelector("#non_contract_add_transactions")
        .addEventListener("shown.bs.modal", initializeModalEvents);

    handleFormPostSubmission(
        "nonContractTransactionsForm",
        "/store-invoice",
        "#non_contract",
        "#non_contract_add_transactions"
    );

    handleFormPostSubmission(
        "paymentForm",
        "/maids-payment",
        "#non_contract",
        "#payment-modal-inv"
    );

    handleFormPostSubmission(
        "creditNoteForm",
        "/credit-note-maidssales",
        "#non_contract",
        "#credit-note-modal"
    );

    updateTotals("[data-unique-id]", "totalCredit", "amount", "qtn", "total");
});

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
                $("#non_contract").DataTable().ajax.reload(null, false);
            }
        },
        error: function (xhr) {
            console.log(xhr.responseText);
            alert(xhr.responseText);
        },
    });
});

// Initialize modal events
function initializeModalEvents() {
    $("#connectionSelect").on("select2:select", function (e) {
        const data = e.params.data.full_data;
        // Ensure groupedData is an array
        const groupedData = Array.isArray(data) ? data : [data];
        addAccountEntriesNonContract(groupedData);
    });
}

// Add multiple account entries
function addAccountEntriesNonContract(groupedData) {
    let container = document.getElementById("entryContainer");
    container.innerHTML = ""; // Clear previous entries if needed

    // Assuming `groupedData` is an array of objects
    groupedData.forEach((connectionData, index) => {
        let entryRow = document.createElement("div");
        entryRow.className = "row mb-3 align-items-end";
        const uniqueId = Date.now() + index;
        entryRow.dataset.uniqueId = uniqueId;

        entryRow.innerHTML = `
            <div class="col">
                <label class="form-label">Service Name:</label>
                <select name="typing_services" class="form-control">
                    <option value="${connectionData.invoice_connection_name}">${connectionData.invoice_connection_name}</option>
                </select>
            </div>
            <div class="col">
                <label class="form-label">Account Ledger:</label>
                <select name="account[]" class="form-control">
                    <option value="${connectionData.ledger}">${connectionData.ledger}</option>
                </select>
            </div>
            <div class="col">
                <label class="form-label">Amount:</label>
                <input type="text" id="amount${uniqueId}" class="form-control" value="${connectionData.amount}">
            </div>
            <div class="col">
                <label class="form-label">Qtn:</label>
                <input type="number" id="qtn${uniqueId}" value="1" class="form-control">
            </div>
            <div class="col">
                <label class="form-label">Notes:</label>
                <input type="text" name="notes[]" class="form-control">
            </div>
            <div class="col">
                <label class="form-label">Total amount:</label>
                <input readOnly type="number" name="total_amount[]" id="total${uniqueId}" class="form-control">
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-danger removeAccountEntry">
                    <i class="bi bi-x-circle"></i>
                </button>
            </div>
        `;
        container.appendChild(entryRow);
        addEventListeners(uniqueId);
    });
    updateTotals("[data-unique-id]", "totalCredit", "amount", "qtn", "total");
}

// Add event listeners to inputs
function addEventListeners(uniqueId) {
    const amountInput = document.getElementById(`amount${uniqueId}`);
    const qtnInput = document.getElementById(`qtn${uniqueId}`);

    [amountInput, qtnInput].forEach((input) => {
        if (input) {
            input.addEventListener("input", () =>
                updateTotals(
                    "[data-unique-id]",
                    "totalCredit",
                    "amount",
                    "qtn",
                    "total"
                )
            );
        }
    });
}

// Remove account entry
$(document).on("click", ".removeAccountEntry", function () {
    let entryRow = $(this).closest(".row");
    entryRow.remove();
    updateTotals("[data-unique-id]", "totalCredit", "amount", "qtn", "total");
});

$(document).on("click", ".open-pay-modal-btn", function () {
    let id = $(this).data("id");

    console.log(id);

    $.ajax({
        url: "/inv/" + id,
        type: "GET",
        success: function (data) {
            $("#payment-modal-inv").modal("show");
            $("#ref_code").val(data.refCode);
            $("#accountInput").val(data?.account_ledger?.ledger);
            $("#maid_nameInput").val(data?.maid_relation?.name);
            $("#idInput").val(id);
        },
        error: function (xhr) {
            alert("Error: " + xhr.responseJSON.message);
        },
    });
});

function formatCreditNoteDataToHtml(data) {
    let htmlContent = "";

    data.forEach(function (item) {
        htmlContent += `
            <div class="credit-note-item">
                <input name=account[] readOnly value="${item.account_ledger.ledger}">
                
                <input name=accountType[]  readOnly value="${item.type}">

                <input name=accountAmount[]  value="${item.amount}">

                <input readOnly name=maidName[]  value="${item?.maid_relation?.name}">

                <input type="hidden" name=refCode[]  id="refCode" >
                
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
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#credit-note-modal").modal("show");
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
            $('#non_contract').DataTable().ajax.reload(null, false);
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