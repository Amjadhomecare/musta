//resources/js/p4/add_contractp4.js
$(document).ready(function () {
    $("#customer_select").select2({
        placeholder: "Search for a customer",
        allowClear: true,
        ajax: {
            url: "/all-customers",
            dataType: "json",
            delay: 250,
            data: function (params) {
                return {
                    search: params.term,
                    page: params.page || 1,
                };
            },
            processResults: function (data) {
                return {
                    results: data.items,
                    pagination: {
                        more: data.total_count > data.items.length,
                    },
                };
            },
            cache: true,
        },
        minimumInputLength: 1,
    });
});

$(document).ready(function () {
    let lastAccruedDate = null;
    let chequeNumber = null;
    let isFirstEntry = true;

    const initializeValues = () => {
        lastAccruedDate = new Date($("#accruedDate").val());
        chequeNumber = Number($("#cheque").val());
    };

    const incrementChequeNumber = () => {
        chequeNumber += 1;
        return chequeNumber;
    };

    const incrementDateByMonth = () => {
        lastAccruedDate.setMonth(lastAccruedDate.getMonth() + 1);
    };

    const formatDate = (dateObj) => {
        const year = dateObj.getFullYear();
        const month = String(dateObj.getMonth() + 1).padStart(2, "0"); // Month is zero-indexed
        const day = String(dateObj.getDate()).padStart(2, "0");
        return `${year}-${month}-${day}`;
    };

    const addAccountEntry = () => {
        if (isFirstEntry) {
            initializeValues();
            isFirstEntry = false;
        } else {
            incrementDateByMonth();
            incrementChequeNumber();
        }

        const formattedDate = formatDate(lastAccruedDate);

        const entryRow = `
            <div class="row mb-3 align-items-end">
                <div class="col">
                    <label class="form-label">Accrued Date MM-DAY-YEAR:</label>
                    <input value="${formattedDate}" type="date" name="cat4date[]" step="0.01" class="form-control">
                </div>
                <div class="col">
                    <label class="form-label">Amount:</label>
                    <input value="${$(
                        "#accruedAmount"
                    ).val()}" type="number" name="amount[]" step="0.01" class="form-control">
                </div>
                <div class="col">
                    <label class="form-label">Notes:</label>
                    <input value="${$(
                        "#note"
                    ).val()}" type="text" name="note[]" class="form-control">
                </div>
                <div class="col">
                    <label class="form-label">Cheques:</label>
                    <input value="${chequeNumber}" type="number" name="cheque[]" class="form-control">
                </div>
            </div>
        `;

        $("#entryContainer").append(entryRow);
        $("#cheque").val(chequeNumber);
    };

    const RecurringJV = () => {
        const numberOfRecurring = $("#RecurringNumber").val();
        for (let i = 0; i < numberOfRecurring; i++) {
            addAccountEntry();
        }
    };

    // Event listeners
    $("#addMore").on("click", RecurringJV);
});
