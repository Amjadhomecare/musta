import { display_table } from "../reuseable/display_table";

let table;

document.addEventListener("DOMContentLoaded", () => {
    // Initialize DataTable using display_table helper for search/export features
    table = display_table("/table-p4", "#p4_expira_dt", [
        { data: "maid_name", name: "maids_d_b_s.name" },
        { data: "passport_number" },
        { data: "maid_type" },
        { data: "passport_expiry" },
        { data: "eid_expiry" },
        { data: "visa_expiry" },
        { data: "labor_card_expiry" },
        { data: "maid_state" },
        { data: "created_by" },
        { data: "updated_by" },
        { data: "created_at" },
        { data: "updated_at" },
        { data: "actions", orderable: false, searchable: false },
    ]);

    // Extend ajax data to include filter parameters
    const originalAjax = table.settings()[0].ajax;
    const originalData = typeof originalAjax.data === 'function' ? originalAjax.data : () => ({});

    table.settings()[0].ajax.data = function (d) {
        // Call original data function
        originalData.call(this, d);

        // Add filter parameters
        d.maid_type = $('#maidTypeFilter').val();
        d.passport_expiring = $('#passportExpiring').is(':checked');
        d.eid_expiring = $('#eidExpiring').is(':checked');
        d.visa_expiring = $('#visaExpiring').is(':checked');
        d.null_passport = $('#nullPassport').is(':checked');
        d.null_eid = $('#nullEid').is(':checked');
        d.null_visa = $('#nullVisa').is(':checked');
        d.labor_card_expiring = $('#laborExpiring').is(':checked');
        d.null_labor_card = $('#nullLabor').is(':checked');
    };

    // Reload table when filters change
    $('#maidTypeFilter, #passportExpiring, #eidExpiring, #visaExpiring, #nullPassport, #nullEid, #nullVisa, #laborExpiring, #nullLabor').on('change', function () {
        table.ajax.reload();
    });

    // Handle edit button click
    $(document).on('click', '.edit-btn', function () {
        const maidId = $(this).data('id');

        // Fetch maid document expiry data
        fetch(`/get-maid-doc-expiry/${maidId}`)
            .then(response => response.json())
            .then(data => {
                // Populate the modal form
                $('#maid_id').val(data.maid_id);
                $('#maid_name').val(data.maid_name);
                $('#labor_card_expiry').val(data.doc_expiry.labor_card_expiry || '');
                $('#passport_expiry').val(data.doc_expiry.passport_expiry || '');
                $('#visa_expiry').val(data.doc_expiry.visa_expiry || '');
                $('#eid_expiry').val(data.doc_expiry.eid_expiry || '');

                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('editDocExpiryModal'));
                modal.show();
            })
            .catch(error => {
                console.error('Error fetching maid data:', error);
                alert('Error loading maid data. Please try again.');
            });
    });

    // Handle save button click
    $('#saveDocExpiry').on('click', function () {
        const formData = {
            maid_id: $('#maid_id').val(),
            labor_card_expiry: $('#labor_card_expiry').val(),
            passport_expiry: $('#passport_expiry').val(),
            visa_expiry: $('#visa_expiry').val(),
            eid_expiry: $('#eid_expiry').val(),
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };

        fetch('/update-maid-doc-expiry', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': formData._token
            },
            body: JSON.stringify(formData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('editDocExpiryModal')).hide();

                    // Show success message
                    alert(data.message);

                    // Reload DataTable
                    $('#p4_expira_dt').DataTable().ajax.reload(null, false);
                } else {
                    alert('Error updating document expiry. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error saving data:', error);
                alert('Error saving data. Please try again.');
            });
    });
});
