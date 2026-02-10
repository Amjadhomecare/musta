import { display_table } from "../reuseable/display_table";
import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";

document.addEventListener("DOMContentLoaded", () => {
    const maidName = document.getElementById("maid-name").dataset.name;

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

    display_table(`/maid/invoices/${maidName}`, "#datatable_invoices", [
        { data: "created_at" },
        { data: "voucher_type" },
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
    ]);
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
});
