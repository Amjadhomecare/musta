import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { display_table } from "../reuseable/display_table";

import { ajaxSelector } from "../ajax_select2";

document.addEventListener("DOMContentLoaded", function () {
    display_table("/table/jv/bulk", "#jv_bulk", [
        { data: "date" },
        { data: "ref" },
        { data: "voucher_type" },
        { data: "account" },
        { data: "post_type" },
        { data: "note" },
        { data: "amount" },
        { data: "action" },
        { data: "delete" },
    ]);

    $("#jv_bulk").on("click", ".open-modal", function () {
        const ref = $(this).data("ref");

        $.ajax({
            url: `/get/jv/${ref}`,
            method: "GET",
            success: function (response) {
                if (response.response && response.response.length > 0) {
                    const firstTran = response.response[0];
                    let modalHeader = `
                        <div class="record-header d-flex flex-wrap align-items-center mb-3">
                            <div class="flex-item me-2">
                                <label for="headerDate" class="form-label">Date</label>
                                <input type="text" class="form-control" id="headerDate" name="tranDate" value="${firstTran.date}" readonly>
                            </div>
                            <div class="flex-item me-2">
                                <label for="headerRef" class="form-label">Ref</label>
                                <input type="text" class="form-control" id="headerRef" name="tranV" value="${firstTran.voucher_type}" readonly>
                            </div>
                            <div class="flex-item me-2">
                                <label for="headerRef" class="form-label">Ref</label>
                                <input type="text" class="form-control" id="headerRef" name="group" value="${firstTran.ref}" readonly>
                            </div>
                        </div>
                    `;

                    $("#modalContent").html(modalHeader);

                    let modalContent = "";

                    response.response.forEach(function (tranData, index) {
                        modalContent += `
                        <div class="record-group d-flex flex-wrap align-items-center mb-3">
                            <div class="flex-item me-2">
                                <select class="form-control" id="postType${index}" name="postType[]">
                                    <option value="${tranData.post_type}">${tranData.post_type}</option>
                                </select>
                            </div>
                            <div class="flex-item me-2">
                                <select class="form-control select2" id="tranAccount${index}" name="tranAccount[]"></select>
                      
                            </div>
                            <div class="flex-item me-2">
                                <select class="form-control select2" id="tranMaid${index}" name="tranMaid[]">
                                    <option value="">Select Maid</option>
                                    <option value="No data" selected>No data</option>
                                </select>
                            </div>
                            <div class="flex-item me-2">
                                <textarea type="text" class="form-control" id="tranAccount${index}" >${tranData.account}</textarea>
                            </div>
                            <div class="flex-item me-2">
                                <textarea type="text" class="form-control" id="tranNote${index}" name="tranNote[]">${tranData.note}</textarea>
                            </div>
                            <div class="flex-item">
                                <label for="tranAmount${index}" class="form-label">Amount</label>
                                <input type="number" class="form-control tranAmount" id="tranAmount${index}" name="tranAmount[]" value="${tranData.amount}" step="0.01">
                            </div>
                        </div>
                        `;
                    });

                    $("#modalContent").append(modalContent);
                    $("#viewModal").modal("show");

                    bindAmountChangeListener();

                    calculateTotals();

                    response.response.forEach(function (_, index) {
                        const selectElement = `#tranAccount${index}`;
                        const maidSelect = `#tranMaid${index}`;
                        ajaxSelector(
                            selectElement,
                            "/add/new/ledger",
                            "Search Ledger",
                            "#viewModal"
                        );

                        $(selectElement).on("select2:open", function () {
                            setTimeout(() => {
                                const select2Container =
                                    $(this).data("select2").$container;
                                const dropdown = $(".select2-dropdown");

                                const containerOffset =
                                    select2Container.offset();
                                const containerWidth =
                                    select2Container.outerWidth();

                                dropdown.css({
                                    position: "fixed",
                                    top: containerOffset.top,
                                    left: containerOffset.left + containerWidth,
                                    width: "auto",
                                });
                            }, 0);
                        });

                        ajaxSelector(
                            maidSelect,
                            "/all/maids",
                            "Search Maid",
                            "#viewModal"
                        );

                        $(maidSelect).on("select2:open", function () {
                            setTimeout(() => {
                                const select2Container =
                                    $(this).data("select2").$container;
                                const dropdown = $(".select2-dropdown");

                                const containerOffset =
                                    select2Container.offset();
                                const containerWidth =
                                    select2Container.outerWidth();

                                dropdown.css({
                                    position: "fixed",
                                    top: containerOffset.top,
                                    left: containerOffset.left + containerWidth,
                                    width: "auto",
                                });
                            }, 0);
                        });
                    });
                } else {
                    $("#modalContent").html(
                        "<p>No records found for this reference.</p>"
                    );
                    $("#viewModal").modal("show");
                }
            },
            error: function () {
                $("#modalContent").html(
                    "<p>Error fetching transaction details.</p>"
                );
                $("#viewModal").modal("show");
            },
        });
    });

    function bindAmountChangeListener() {
        $(".tranAmount").on("input", function () {
            calculateTotals();
        });
    }

    function calculateTotals() {
        let totalDebit = 0;
        let totalCredit = 0;

        $(".tranAmount").each(function () {
            const amount = parseFloat($(this).val()) || 0;
            const postType = $(this)
                .closest(".record-group")
                .find("select[name='postType[]']")
                .val();

            if (postType === "debit") {
                totalDebit += amount;
            } else if (postType === "credit") {
                totalCredit += amount;
            }
        });

        $("#totalDebit").text(totalDebit.toFixed(2));
        $("#totalCredit").text(totalCredit.toFixed(2));
        toggleSubmitButton(totalDebit, totalCredit);
    }

    function toggleSubmitButton(totalDebit, totalCredit) {
        const addButton = $("#transactionForm button[type='submit']");
        if (totalDebit !== totalCredit) {
            addButton.prop("disabled", true);
        } else {
            addButton.prop("disabled", false);
        }
    }

    $("#jv_bulk").on("click", ".open-modal-delete", function () {
        const ref = $(this).data("ref");

        $("#deleteBulkModal").modal("show");
        $("#refInput").val(ref);
    });

    handleFormPostSubmission(
        "transactionForm",
        "/store/bulk",
        "#jv_bulk",
        "#viewModal"
    );

    handleFormPostSubmission(
        "deleteRef",
        "/bulk-delete",
        "#jv_bulk",
        "#deleteBulkModal"
    );
});
