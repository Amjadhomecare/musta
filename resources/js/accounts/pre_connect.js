import { ajaxSelector } from "../ajax_select2";

function addAccountEntry() {
    let container = document.getElementById("entryContainer");
    let entryRow = document.createElement("div");
    entryRow.className = "row mb-3 align-items-end";
    entryRow.innerHTML = `
        <div class="col">
            <label class="form-label">Account Ledger:</label>
            <select name="account[]" class="form-control ajax-account-select"></select>
        </div>
        <div class="col">
            <label class="form-label">Type:</label>
            <select name="type[]" onchange="updateTotals()" class="form-select">
                <option value="debit">Debit</option>
                <option value="credit">Credit</option>
            </select>
        </div>
        <div class="col">
            <label class="form-label">Amount:</label>
            <input type="text" name="amount[]" step="0.01" onchange="updateTotals()" class="form-control">
        </div>
        <div class="col">
            <label class="form-label">Notes:</label>
            <input type="text" name="notes[]" class="form-control">
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

window.RecurringJV = function () {
    let numberOfRecurring = parseInt(document.getElementById("RecurringNumber").value, 10);
    console.log("Number of Recurring Entries: ", numberOfRecurring);

    for (let i = 0; i < numberOfRecurring; i++) {
        console.log("Adding entry number: ", i + 1);
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

        if (type === "debit") {
            acc.totalDebit += amount;
        } else {
            acc.totalCredit += amount;
        }

        return acc;
    }, { totalDebit: 0, totalCredit: 0 });

    document.getElementById("totalDebit").textContent = totals.totalDebit.toFixed(2);
    document.getElementById("totalCredit").textContent = totals.totalCredit.toFixed(2);

    let submitButton = document.querySelector('input[type="submit"]');
    submitButton.disabled = totals.totalDebit !== totals.totalCredit;
};


$(document).ready(function() {
    let dataTable = $('#connection-invoice-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/ajax-list-invoice-connection'
        },
        columns: [
            { data: 'created_at', name: 'created_at' }, 
            { data: 'group', name: 'group' }, 
            { data: 'invoice_connection_name', name: 'invoice_connection_name' }, 
            { data: 'ledger', name: 'ledger' }, 
            { data: 'amount', name: 'amount' }, 
            { data: 'notes', name: 'notes' }, 
            { data: 'total_credit', name: 'total_credit' },
            {
                data: 'id',
                render: function(data, type, row) {
                    return `<a target='_blank' href='/invoice-connection-edit/${row.invoice_connection_name}'>Edit</a>
                    <a  href='/delete-invoice-connection/${row.invoice_connection_name}'>Delete</a>
                                 `;
                }

            } 
   
             ],
                order: [[0, 'desc']],
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                dom: 'Blfrtip'
            });
});



$(document).ready(function() {
    let dataTable = $('#connection-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/ajax-list-pre-connection'
        },
        columns: [
            { data: 'created_at', name: 'created_at' }, 
            { data: 'name_of_connection', name: 'name_of_connection' }, 
            { data: 'account', name: 'account' }, 
            { data: 'amount', name: 'amount' }, 
            { data: 'notes', name: 'notes' }, 
       
            {
                data: 'id',
                render: function(data, type, row) {
                    return `<a target='_blank' href='/pre-connection-edit/${row.name_of_connection}'>Edit</a>
                    <a  href='/delete-jv-connection/${row.name_of_connection}'>Delete</a>
                                 `;
                }

            } 
   
             ],
                order: [[0, 'desc']],
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                dom: 'Blfrtip'
            });
});

