import { ajaxSelector } from "../ajax_select2";

function addAccountEntry() {
    let container = document.getElementById("entryContainer");
    let entryRow = document.createElement("div");
    entryRow.className = "row mb-3 align-items-end";
    entryRow.innerHTML = `
        <div class="col">
            <label class="form-label">Account Ledger:</label>
            <select name="ledger_name[]" class="form-control ajax-account-select"></select>
        </div>
        <div class="col">
            <label class="form-label">Type:</label>
            <select name="type[]" onchange="updateTotals()" class="form-select">
                <option value="credit">Credit</option>
            </select>
        </div>
        <div class="col">
            <label class="form-label">Amount:</label>
            <input type="text" name="amount[]" step="0.01" onchange="updateTotals()" class="form-control">
        </div>
        <div class="col">
            <label class="form-label">Notes:</label>
            <input type="text" name="note[]" class="form-control">
        </div>
        <div class="col-auto">
            <button type="button" onclick="removeAccountEntry(this)" class="btn btn-danger">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>
    `;
    container.appendChild(entryRow);

    ajaxSelector(
        $(entryRow).find('.ajax-account-select'),
        "/add/new/ledger",
        "Search for a Ledger",
        "#entryContainer"
    );
}

window.RecurringINV = function () {
    let numberOfRecurring = parseInt(document.getElementById("RecurringNumber").value, 10);
    for (let i = 0; i < numberOfRecurring; i++) {
        addAccountEntry();
    }
};

window.removeAccountEntry = function (button) {
    let entryRow = button.parentNode.parentNode;
    entryRow.remove();
    updateTotals();
};

window.updateTotals = function () {
    let entries = document.getElementsByClassName("row mb-3 align-items-end");

    let totals = Array.from(entries).reduce((acc, entry) => {
        let type = entry.querySelector('[name="type[]"]').value;
        let amount = parseFloat(entry.querySelector('[name="amount[]"]').value) || 0;
        if (type === "credit") {
            acc.totalCredit += amount;
        }
        return acc;
    }, { totalCredit: 0 });

    document.getElementById("totalCredit").textContent = totals.totalCredit.toFixed(2);
    document.getElementById("hiddenTotalCredit").value = totals.totalCredit.toFixed(2);
};
