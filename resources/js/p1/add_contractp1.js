//resources/js/p1/add_contractp1.js

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
    let preConnectionData = [];

    window.handleConnectionChange = function (invoice_connection_name) {
        $.ajax({
            url: `/fetch-per-connection/${invoice_connection_name}`,
            method: "GET",
            dataType: "json",
            success: function (data) {
                preConnectionData = data;
                processConnections(preConnectionData);
            },
            error: function (xhr, status, error) {
                console.error(
                    "An error occurred while fetching connection data:",
                    error
                );
            },
        });

        window.handleConnectionChange = function (invoice_connection_name) {
            $.ajax({
                url: `/fetch-per-connection/${invoice_connection_name}`,
                method: "GET",
                dataType: "json",
                success: function (data) {
                    $("#entryContainer").empty();
                    preConnectionData = data;
                    processConnections(preConnectionData);
                },
                error: function (xhr, status, error) {
                    console.error(
                        "An error occurred while fetching connection data:",
                        error
                    );
                },
            });
        };
    };

    function processConnections(connections) {
        connections.forEach((connection, index) => {
            addAccountEntry(connection, index);
        });
    }

    window.addAccountEntry = function (connectionData = null, index) {
        const uniqueId = Date.now() + index;

        const entryRow = `
        <div class="row mb-3 align-items-end" data-unique-id="${uniqueId}">
            <div class="col">
                <label class="form-label">Service Name:</label>
                <select name="service" class="form-control">
                    <option value="${connectionData.invoice_connection_name}">${
            connectionData.invoice_connection_name
        }</option>
                </select>
            </div>

            <div class="col">
                <label class="form-label">Account Ledger:</label>
                <select name="account[]" class="form-control">
                    <option value="${connectionData.ledger}">${
            connectionData.ledger
        }</option>
                </select>
            </div>

            <div class="col">
                <label class="form-label">Amount:</label>
                <input type="number" name="amount[]" class="form-control amount-field" step="0.01" value="${
                    connectionData ? connectionData.amount : ""
                }">
            </div>

            <div class="col">
                <label class="form-label">Notes:</label>
                <input type="text" name="notes[]" class="form-control">
            </div>

            <div class="col-auto">
                <button type="button" class="btn btn-danger remove-entry">
                    <i class="bi bi-x-circle"></i>
                </button>
            </div>
        </div>
        `;

        $("#entryContainer").append(entryRow);

        // Attach the input event to the new Amount field
        $(`div[data-unique-id="${uniqueId}"] .amount-field`).on(
            "input",
            function () {
                updateTotals();
            }
        );

        updateTotals();
    };

    window.updateTotals = function () {
        let totalAmount = 0;
        $('input[name="amount[]"]').each(function () {
            totalAmount += parseFloat($(this).val()) || 0;
        });

        $("#totalCredit").val(totalAmount.toFixed(2));
    };

    $(document).on("click", ".remove-entry", function () {
        $(this).closest(".row").remove();
        updateTotals();
    });
});
