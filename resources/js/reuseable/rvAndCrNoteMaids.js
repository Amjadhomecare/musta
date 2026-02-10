export const rvAndCrNoteForMaids = $(document).on(
    "click",
    "#payment-modal-inv",
    function () {
        let id = $(this).data("id");

        $.ajax({
            url: "/inv/" + id,
            type: "GET",
            success: function (data) {
                $("#payment-modal").modal("show");
                $("#ref_code").val(data.refCode);
                $("#accountInput").val(data.account_ledger.ledger);
                $("#maid_nameInput").val(data.maid_relation.name);
                $("#idInput").val(id);
            },
            error: function (xhr) {
                alert("Error: " + xhr.responseJSON.message);
            },
        });
    }
);

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
