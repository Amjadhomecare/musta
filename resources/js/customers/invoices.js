import { display_table, updateTotals } from "../reuseable/display_table";
import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import {
    initializeModalSelect2,
    initializeSelect2,
} from "../reuseable/slelect2_for_modal";

document.addEventListener("DOMContentLoaded", () => {
    const customerName = document.getElementById("customer-name").dataset.name;

    handleFormPostSubmission(
        "paymentForm",
        "/receive-payment",
        "#datatable_invoices",
        "#typing-payment-modal"
    );

    handleFormPostSubmission(
        "creditNoteForm",
        "/credit-note-maidssales",
        "#datatable_invoices",
        "#typing-credit-note-modal"
    );

    display_table(`/customer/invoices/${customerName}`, "#datatable_invoices", [
        { data: "date" },
        { data: "voucher_type" },
        { data: "refCode" },
        { data: "contract_ref" },
        { data: "account" },
        { data: "maid_name" },
        { data: "pre_connection_name" },
        { data: "amount" },
        { data: "invoice_balance" },
        { data: "payment_status", searchable: false },
        { data: "notes" },
        { data: "receiveRef" },
        { data: "creditNoteRef" },
        { data: "created_by" },
        { data: "created_at" },
        { data: "action", orderable: false, searchable: false },
    ]);
});

function applyFilters() {
    let filterVoucher = $("#filterV").val();

    $("#datatable_invoices").DataTable().column(1).search(filterVoucher).draw();
}

$("#filterV").on("change", applyFilters);

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
                $("#datatable_invoices").DataTable().ajax.reload(null, false);
            }
        },
        error: function (xhr) {
            console.log(xhr.responseText);
            alert(xhr.responseText);
        },
    });
});

document.addEventListener("DOMContentLoaded", () => {
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
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
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



// this for adding new invoice

    
    initializeSelect2(
        "#selected_maid",
        "/all/maids",
        "Search for a maid",
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
        "#datatable_invoices",
        "#non_contract_add_transactions"
    );
    
        updateTotals("[data-unique-id]", "totalCredit", "amount", "qtn", "total");
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














