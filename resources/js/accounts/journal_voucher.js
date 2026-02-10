import { ajaxSelector } from "../ajax_select2";

import { display_table } from "../reuseable/display_table";

if ($.fn.DataTable.isDataTable("#general-journal-voucher-table")) {
    // If DataTable is initialized, destroy the existing instance
    $("#general-journal-voucher-table").DataTable().destroy();
}

document.addEventListener("DOMContentLoaded", function () {
    const columns = [
        { data: "id", name: "id", visible: false },
        { data: "date", name: "date" },
        { data: "voucher_type", name: "voucher_type" },
        { data: "refCode", name: "refCode" },
        { data: "maid_name" },
        { data: "account", name: "account" },
        { data: "notes", name: "notes" },
        { data: "type", name: "type" },
        { data: "amount", name: "amount" },

        {
            data: null,
            name: "action",
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                return `<a target="__blank" href="/view/jv/selected/${row.refCode}" class="btn btn-blue rounded-pill waves-effect waves-light">
                        <i class="fa fa-eye" aria-hidden="true">View</i>
                    </a>
                    <a  href="#" class="btn btn-blue rounded-pill waves-effect waves-light edit-jv" title="edit "  data-refCode ="${row.refCode}">
                        <i class="fa fa-pencil" aria-hidden="true">Edit</i>
                    </a>`;
            },
        },
    ];

    display_table("/all-jv", "#general-journal-voucher-table", columns);

    function applyFilters() {
        let filter = $("#filterVoucher").val();

        $("#general-journal-voucher-table")
            .DataTable()
            .column(2)
            .search(filter)
            .draw();
    }

    $("#filterVoucher").on("change", applyFilters);
});

// Initialize the Select2 component
if ($("#connectionSelect").length) {
    ajaxSelector(
        "#connectionSelect",
        "/all/general/jv",
        "Search for a Connection",
        "#entryContainer"
    );
} else {
    console.log("Select2 initialization skipped: Element missing.");
}

$("#connectionSelect").on("select2:select", function (e) {
   let selectedConnection = e.params.data.id;
    handleConnectionChange(selectedConnection);
});

function handleConnectionChange(connectionName) {
    $.ajax({
        url: "/pre_connection/accounting",
        type: "GET",
        data: {
            name_of_connection: connectionName,
        },
        success: function (response) {
            if (Array.isArray(response)) {
                handleEntries(response);
            }
        },
        error: function (xhr, status, error) {
            console.error("An error occurred: " + error);
            alert("Failed to fetch data: " + xhr.responseText);
        },
    });
}
// Async function to handle entries
async function handleEntries(response) {
    for (const [index, item] of response.entries()) {
        try {
            const uniqueId = await addAccountEntry(item, index);
            if (uniqueId === undefined) {
                console.error("Failed to generate uniqueId for:", item);
                continue; // Skip this iteration if uniqueId is undefined
            }
            ajaxSelector(
                `#accountLedger${uniqueId}`,
                "/add/new/ledger",
                "Search for an account ledger",
                `#entryContainer`
            );
            ajaxSelector(
                `#maidSelect${uniqueId}`,
                "/all/maids",
                "Search for a maid",
                `#entryContainer`
            );
        } catch (error) {
            console.error("Error processing entry:", error);
        }
    }
}

function addAccountEntry(connectionData = null, index) {
    return new Promise((resolve) => {
        let container = document.getElementById("entryContainer");
        let entryRow = document.createElement("div");
        entryRow.className = "row mb-3 align-items-end";
        const uniqueId = Date.now() + index;
        entryRow.setAttribute("data-unique-id", uniqueId);
        entryRow.innerHTML = `
            <div class="col-md-5">
                <label for="accountLedger${uniqueId}" class="form-label">Account Ledger:</label>
                <select id="accountLedger${uniqueId}" class="form-select" name="account[]"></select>
            </div>
            <div class="col-md-3">
                <label for="maidSelect${uniqueId}" class="form-label">Maid:</label>
                <select id="maidSelect${uniqueId}" class="form-select" name="maid[]">
                    <option value="No Maid" selected>No maid</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="type${uniqueId}" class="form-label">Type:</label>
                <select id="type${uniqueId}" name="type[]" onChange="updateTotals()" class="form-select">
                    <option value="debit" ${connectionData && connectionData.type === "debit" ? "selected" : ""}>Debit</option>
                    <option value="credit" ${connectionData && connectionData.type === "credit" ? "selected" : ""}>Credit</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="amount${uniqueId}" class="form-label">Amount:</label>
                <input type="number" name="amount[]" id="amount${uniqueId}" step="0.01" onChange="updateTotals()" class="form-control" value="${connectionData ? connectionData.amount : ""}">
            </div>
            <div class="col-md-2">
                <label for="selecting${uniqueId}" class="form-label">VAT Type:</label>
                <select id="selecting${uniqueId}" onChange="updateTotals()" class="form-select">
                    <option value="no vat">no vat</option>
                    <option value="exclusive">exclusive</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="vat${uniqueId}" class="form-label">VAT:</label>
                <input type="number" step="0.01" name="vatpayable[]" id="vat${uniqueId}" class="form-control" readonly>
            </div>
            <div class="col-md-2">
                <label for="net${uniqueId}" class="form-label">Net Amount:</label>
                <input type="number" id="net${uniqueId}" class="form-control" readonly>
            </div>
            <div class="col-md-3">
                <label for="notes${uniqueId}" class="form-label">Notes:</label>
                <input type="text" name="notes[]" id="notes${uniqueId}" class="form-control" value="${connectionData ? connectionData.notes : ""}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger removeAccountEntry">
                    <i class="bi bi-x-circle"></i>
                </button>
            </div>
        `;

        container.appendChild(entryRow);

        // Now fill in the account select options and select the correct one
        const accountSelect = document.getElementById(`accountLedger${uniqueId}`);
        if (connectionData && connectionData.account) {
            let option = new Option(
                connectionData.account,
                connectionData.account,
                true,
                true
            );
            accountSelect.appendChild(option);
        }

        resolve(uniqueId); // Resolve the promise AFTER the element is appended
        updateTotals();
    });
}


const calculateVAT = (vatOption, amount) => {
    if (isNaN(amount)) {
        return { vat: "", net: "" };
    }

    let vat, net;

    if (vatOption === "inclusive") {
        vat = parseFloat((amount - amount / 1.05).toFixed(2));
        net = parseFloat((amount / 1.05).toFixed(2));
    } else if (vatOption === "exclusive") {
        vat = parseFloat((amount * 0.05).toFixed(2));
        net = parseFloat((amount * 1.05).toFixed(2));
    } else {
        vat = 0;
        net = amount;
    }

    return { vat, net };
};

var addButton = document.getElementById("addEntryButton");
if (addButton) {
    addButton.addEventListener("click", RecurringJV);
} else {
    console.log("Button not found on the page.");
}

function RecurringJV() {
    let numberOfRecurring = parseInt(
        document.getElementById("RecurringNumber").value,
        10
    ); // Convert to integer
    if (isNaN(numberOfRecurring)) {
        alert("Please enter a valid number");
        return;
    }

    if (numberOfRecurring > 50) {
        alert("Cannot allow more than 50 entries.");
        return;
    }

    for (let i = 0; i < numberOfRecurring; i++) {
        addAccountEntry(null, i).then((uniqueId) => {
            // Assuming ajaxSelector needs to be called after each entry is added
            ajaxSelector(
                `#accountLedger${uniqueId}`,
                "/add/new/ledger",
                "Search for an account ledger",
                `#entryContainer`
            );
            ajaxSelector(
                `#maidSelect${uniqueId}`,
                "/all/maids",
                "Search for a maid",
                `#entryContainer`
            );
        });
    }
}

$(document).on("click", ".removeAccountEntry", function () {
    let entryRow = $(this).closest(".row");
    entryRow.remove();
    updateTotals();
});

window.updateTotals = function () {
    // Step 2: Only select entry rows with a unique id
    let entries = document.querySelectorAll('.row.mb-3.align-items-end[data-unique-id]');

    let totals = Array.from(entries).reduce(
        (acc, entry) => {
            // Step 3: Defensive check for uniqueId
            let uniqueId = entry.dataset.uniqueId;
            if (!uniqueId) {
                console.error("uniqueId not found for entry", entry);
                return acc;
            }

           let amountInput = entry.querySelector(`input#amount${uniqueId}`);

            if (!amountInput) {
                console.error(`Amount input not found for uniqueId: ${uniqueId}`, entry);
                return acc;
            }

            let type = entry.querySelector('[name="type[]"]').value;
            let amount = parseFloat(amountInput.value) || 0;
            let vatType = entry.querySelector(`#selecting${uniqueId}`).value;

            const { vat, net } = calculateVAT(vatType, amount);
            entry.querySelector(`#vat${uniqueId}`).value = vat;
            entry.querySelector(`#net${uniqueId}`).value = net;

            if (type === "debit") {
                acc.totalDebit += net;
            } else {
                acc.totalCredit += net;
            }

            return acc;
        },
        { totalDebit: 0, totalCredit: 0 }
    );

    document.getElementById("totalDebit").textContent =
        totals.totalDebit.toFixed(2);
    document.getElementById("totalCredit").textContent =
        totals.totalCredit.toFixed(2);

    let submitButton = document.getElementById("submit_add_journal_Voucher");

    if (submitButton) {
        submitButton.disabled = totals.totalDebit !== totals.totalCredit;
    }
};




$(document).on("click", "#submit_add_journal_Voucher", function (e) {
    e.preventDefault();

    $(".btn-success").prop("disabled", true).val("Submit...");

    // Use FormData instead of serialize
    const journalVoucherForm = document.getElementById("journalVoucherForm");
    const formData = new FormData(journalVoucherForm);

    $.ajax({
        type: "POST",
        url: "/jv",
        data: formData,
        processData: false,       // IMPORTANT!
        contentType: false,       // IMPORTANT!
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            Accept: "application/json",
        },
        success: function (data) {
            if (data.success) {
                $("#add_journal_voucher").modal("hide");
                $("#journalVoucherForm")[0].reset();
                $("#entryContainer").empty();
                $("#totalDebit").text("0.00");
                $("#totalCredit").text("0.00");
                $("#connectionSelect").empty();

                $(".btn-success").prop("disabled", false).val("Submit");
                $("#general-journal-voucher-table")
                    .DataTable()
                    .ajax.reload(null, false);
                toastr.success(data.message);
            } else {
                toastr.options = {
                    positionClass: "toast-top-full-width",
                };
                toastr.error(data.message);
                $(".btn-success").prop("disabled", false).val("Submit");
            }
        },
        error: function (xhr) {
            toastr.options = {
                positionClass: "toast-top-full-width",
            };
            toastr.error("An error occurred. Please try again.");
            $(".btn-success").prop("disabled", false).val("Submit");
        }
    });
});



function updateTotalsForEditForm() {
    let totalDebit = 0,
        totalCredit = 0;

    Array.from(
        document.querySelectorAll(".edit-voucher-table tbody tr")
    ).forEach((row) => {
        let type = row.querySelector(
            'select[name^="transactions"][name$="[type]"]'
        ).value;
        let amount =
            parseFloat(
                row.querySelector(
                    'input[name^="transactions"][name$="[amount]"]'
                ).value
            ) || 0;

        if (type.toLowerCase() === "debit") {
            totalDebit += amount;
        } else if (type.toLowerCase() === "credit") {
            totalCredit += amount;
        }
    });

    document.getElementById("edit_total_Debit").textContent =
        totalDebit.toFixed(2);
    document.getElementById("edit_total_credit").textContent =
        totalCredit.toFixed(2);

    // Adjust logic for enabling the submit button based on balance
    // let isBalanced = Math.abs(totalDebit - totalCredit) < 0.01;
    let isBalanced = totalDebit !== totalCredit;
    document.getElementById("submit_edit_jv").disabled = isBalanced;
}

$(document).on("click", ".edit-jv", function (e) {
    e.preventDefault();
   let refCode = $(this).attr("data-refCode");

    $.ajax({
        type: "GET",
        url: "/view/jv/edit/" + refCode,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            Accept: "application/json",
        },
        success: function (data) {
            if (data.success) {
                // Assume data.details_jv contains all the entries
               let details = data.details_jv;
                // Clear existing rows in the table
               let tbody = $("#voucher-details-table tbody");
                tbody.empty();

                // Set header information
                $("#edit_voucher_type").val(details[0].voucher_type);
                $('input[name="refNumber"]').val(
                    "Ref Number : " + details[0].refNumber
                );
                $('input[name="date"]').val(details[0].date);

                // Append new rows to the table
                details.forEach(function (detail, index) {
                   let createdDate = new Intl.DateTimeFormat("en-US", {
                        year: "numeric",
                        month: "2-digit",
                        day: "2-digit",
                        // hour: '2-digit', minute: '2-digit', second: '2-digit',
                        hour12: false, // Use 24-hour time
                    }).format(new Date(detail.created_at));

                   let updatedDate = new Intl.DateTimeFormat("en-US", {
                        year: "numeric",
                        month: "2-digit",
                        day: "2-digit",
                        // hour: '2-digit', minute: '2-digit', second: '2-digit',
                        hour12: false,
                    }).format(new Date(detail.updated_at));

                   let row = `<tr class="bg-light">
                                        <td class="border-bottom"><input readonly type="text" class="form-control" name="transactions[${index}][id]" value="${detail.id}"></td>
                                        <td class="border-bottom">
                                            <select class="form-select" name="transactions[${index}][type]">
                                                <option value="${detail.type}" selected>${detail.type}</option>
                                                <option value="debit">debit</option>
                                                <option value="credit">credit</option>
                                            </select>
                                        </td>
                                        <td class="border-bottom">
                                            <select class="form-select account-select" data-index="${index}" name="transactions[${index}][account]">
                                                <option value="${detail.account}" selected>${detail.account}</option>
                                            </select>
                                        </td>

                                        <td class="border-bottom">
                                            <select class="form-select maid-select" data-index="${index}" name="transactions[${index}][maid_name]">
                                                <option value="${detail.maid_name}" selected>${detail.maid_name}</option>
                                            </select>
                                        </td>
                                        
                                      <td class="border-bottom">
                                            <input type="number" class="form-control" name="transactions[${index}][amount]" value="${detail.amount}" style="min-width:100px;">
                                        </td>


                                        <td class="border-bottom"><input type="number" class="form-control" name="transactions[${index}][invoice_balance]" value="${detail.invoice_balance}"></td>

                                        <td class="border-bottom"><input type="text" class="form-control" name="transactions[${index}][notes]" value="${detail.notes}"  ></td>
                                        <td class="border-bottom">${createdDate}</td>
                                        <td class="border-bottom">${updatedDate}</td>
                                    </tr>`;
                    tbody.append(row);
                });
                // Initialize Select2 with ajaxSelector
                $(".account-select").each(function () {
                    ajaxSelector(
                        this,
                        "/add/new/ledger",
                        "Search for a account",
                        "#edit_journal_voucher .modal-body",
                        "account-selection"
                    );
                });

                $(".maid-select").each(function () {
                    ajaxSelector(
                        this,
                        "/all/maids",
                        "Search for a maid",
                        "#edit_journal_voucher .modal-body",
                        "account-selection"
                    );
                });

                document
                    .querySelector(".edit-voucher-table")
                    .addEventListener("input", updateTotalsForEditForm);

                updateTotalsForEditForm();

                // Show the modal
                $("#edit_journal_voucher").modal("show");
            } else {
                toastr.error(data.message, {
                    positionClass: "toast-top-full-width",
                });
            }
        },
    });
});

$(document).on("click", "#submit_edit_jv", function (e) {
    e.preventDefault();

        const form = document.getElementById("editJournalVoucherForm");
        const formData = new FormData(form);

        $.ajax({
            type: "POST",
            url: "/jv/update",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                Accept: "application/json",
            },
            success: function (data) {
                if (data.success) {
                    $("#edit_journal_voucher").modal("hide");
                    $("#general-journal-voucher-table")
                        .DataTable()
                        .ajax.reload(null, false);
                    toastr.success(data.message);
                } else {
                    toastr.options = {
                        positionClass: "toast-top-full-width",
                    };
                    toastr.error(data.message);
                }
            },
        });
});



document.addEventListener("keydown", function(event) {
    // Open the modal and focus on the select input when "M" is pressed
    if (event.key.toLowerCase() === "m" && !event.target.matches("input, textarea, select")) {
        event.preventDefault();
        let modalButton = document.querySelector(".open-modal-btn");
        if (modalButton) {
            modalButton.click(); // Open modal
        }
    }

    // When "A" is pressed inside the modal, trigger the Add Entry button
    if (event.key.toLowerCase() === "a" && !event.target.matches("input, textarea, select")) {
        event.preventDefault();
        let addEntryButton = document.getElementById("addEntryButton");
        if (addEntryButton) {
            addEntryButton.click(); 
        }
    }
});


document.addEventListener("DOMContentLoaded", function() {
    let modal = document.getElementById("add_journal_voucher"); 
    if (modal) {
        modal.addEventListener("shown.bs.modal", function () {
            let voucherSelect = document.getElementById("voucher_type");
            if (voucherSelect) {
                voucherSelect.focus();
            }
        });
    }
});
