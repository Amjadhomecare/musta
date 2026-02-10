import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";

$(document).ready(function () {
  let table;

  $('#payrollForm').submit(function (event) {
    event.preventDefault();

    const selectedMonth = $('#month').val();
    if (!selectedMonth) {
      alert('Please select a month.');
      return;
    }

    const startDate = `${selectedMonth}-01`;
    let endDateObj = new Date(`${selectedMonth}-01`);
    endDateObj.setMonth(endDateObj.getMonth() + 1);
    endDateObj.setDate(0);
    const endDate = endDateObj.toISOString().split('T')[0];

    if (table) table.destroy();
    loadTable(startDate, endDate);
  });

  $('.filter-checkbox, #paymentStatusFilter, #workingDaysFilter, #maidStatus, #maidType, #maidPayment, #branch')
    .on('change', function () {
      if (table) table.ajax.reload();
    });

  function loadTable(startDate, endDate) {
    table = $('#payrollTable').DataTable({
      responsive: true,
      processing: true,
      serverSide: true,
      stateSave: true,

      language: {
        searchPlaceholder: 'Search maid…',
        search: '',
        lengthMenu: '_MENU_'
      },

      initComplete: function () {
        $('#payrollTable_length select').addClass('ms-2');

        const $filter = $('#payrollTable_filter');
        const $input = $filter.find('input');

        $filter.addClass('ps-10');
        $input.removeClass('form-control-sm').addClass('form-control-lg py-3');
      },

      ajax: {
        url: '/get-payroll',
        data: function (d) {
          d.start_date = startDate;
          d.end_date = endDate;
          d.maid_status = $('#maidStatus').val();
          d.maid_type = $('#maidType').val();
          d.maid_payment = $('#maidPayment').val();
          d.working_days_filter = $('#workingDaysFilter').val();
          d.payment_status = $('#paymentStatusFilter').val();
          d.branch = $('#branch').val();
          d.filter_no_note_no_booked = $('#filterNoNoteNoBooked').is(':checked') ? 1 : 0;
          d.filter_booked = $('#filterBooked').is(':checked') ? 1 : 0;
          d.filter_note = $('#filterNote').is(':checked') ? 1 : 0;
        },
        error: function (xhr) {
          alert(xhr.responseJSON?.error || 'Failed to load payroll data.');
        }
      },

      columns: [
        {
          data: null,
          defaultContent: '',
          render: function (data, type, row) {
            const maidId = row.maid_id;                 // ← ID from backend
            const maidName = row.maid;                  // ← human name
            const totalDays = Number(row.total_days_difference) || 0;
            const salary = Number(row.salary) || 0;
            const deduction = Number(row.deduction) || 0;
            const allowance = Number(row.Allowance) || 0;
            const net = Math.round(salary / 30) * totalDays - deduction + allowance;

            return `
              <input type="checkbox" class="maid-checkbox"
                     value="${maidId}"
                     data-maid-id="${maidId}"
                     data-maid="${maidName}"
                     data-totaldays="${totalDays}"
                     data-deduction="${deduction}"
                     data-allowance="${allowance}"
                     data-note="${row.note ?? ''}"
                     data-salary="${salary}"
                     data-net="${net}"
                     data-type="${row.latest_maid_type ?? ''}"
                     data-method="${row.payment ?? ''}"
                     data-status="${row.latest_maid_status ?? ''}">
            `;
          },
          orderable: false
        },

        { data: 'maid_moi', title: 'Moi' },

        {
          data: 'maid',
          title: 'Maid',
          render: function (data, type, row) {
            // switch link to ID-based route (keeps showing the name)
            return `<a href="/maid-report/p4/${row.maid}" target="_blank">${row.maid}</a>`;
          }
        },

        { data: 'salary', title: 'Basick Salary' },

        {
          data: 'payment',
          title: 'Method',
          render: function (data, type, row, meta) {
            if (type === 'display' || type === 'filter') {
              const options = `
                <option value="cash" ${data === 'cash' ? 'selected' : ''}>Cash</option>
                <option value="bank" ${data === 'bank' ? 'selected' : ''}>Bank</option>
              `;
              return `<select class="form-select payment-select" style="min-width: 100px;" data-row-index="${meta.row}">${options}</select>`;
            }
            return data;
          }
        },

        { data: 'latest_maid_status', title: 'Maid Status' },
        { data: 'latest_maid_type', title: 'Maid Type' },
        { data: 'total_days_difference', title: 'Total Days Difference' },

        {
          data: 'paid',
          title: 'Paid',
          render: function (data) {
            return data ? 'Paid' : 'Unpaid';
          }
        },

        {
          data: 'latest_customer',
          title: 'Cus',
          render: function (data, type, row) {
            return `<a href="/customer/report/p4/${row.latest_customer}" target="_blank">${row.latest_customer}</a>`;
          }
        },

        { data: 'maid_branch', title: 'Visa under' },
        { data: 'maid_booked', title: 'booked' },
        { data: 'note', title: 'note' },
        { data: 'deduction', title: 'deduction' },
        { data: 'Allowance', title: 'Allowance' },

        {
          data: null,
          title: 'Net Salary',
          render: function (data, type, row) {
            const salary = Number(row.salary) || 0;
            const totalDays = Number(row.total_days_difference) || 0;
            const deduction = Number(row.deduction) || 0;
            const allowance = Number(row.Allowance) || 0;
            return Math.round(salary / 30) * totalDays - deduction + allowance;
          }
        },

        { data: 'latest_date', title: 'Latest Date' },

        {
          data: 'latest_contract',
          render: function (data, type, row) {
            return `<a href="/category4/contract-bycontract/${row.latest_contract}" target="_blank">${row.latest_contract}</a>`;
          }
        },

        {
          data: 'idForDeduction',
          render: function (data, type, row) {
            if (row.idForDeduction == null) {
              // include maid_id so your modal can save by ID
        // Add button
                return `<button type="button"
                class="btn btn-blue btn-sm open-new-btn"
                data-bs-toggle="modal"
                data-bs-target="#add-maid-deduction-modal"
                data-maid_id="${row.maid_id}"
                data-maid_name="${row.maid}">Add ${row.maid}</button>`;
            } else {
               // Edit button
                    return `<button type="button"
                    class="btn btn-primary btn-sm open-modal-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#maid-dedction"
                    data-deduction="${row.deduction}"
                    data-allowance="${row.Allowance}"
                    data-note="${row.note ?? ''}"
                    data-id="${row.idForDeduction}"
                    data-maid_id="${row.maid_id}"
                    data-maid_name="${row.maid}">Edit</button>`;

            }
          }
        },

        { data: 'paid_by', title: 'Paid by' },
        { data: 'paid_at', title: 'Paid at' },
        { data: 'paid_note', title: 'Paid note' },
        { data: 'nationality', title: 'Nationality' },
        { data: 'start', title: 'Start' }
      ],

      dom:
        "<'row mb-3'<'col-md-6 d-flex align-items-center'f>" +
        "<'col-md-6 d-flex justify-content-end align-items-center gap-2'" +
        "<'d-flex align-items-center gap-2 dt-toolbar' B l>>" +
        "<'table-responsive'tr>" +
        "<'row mt-3'<'col-md-5'i><'col-md-7 d-flex justify-content-end'p>>",

      pagingType: 'full_numbers',
      pageLength: 10,
      lengthMenu: [
        [10, 50, 100, 300, 600, -1],
        [10, 50, 100, 300, 600, 'All']
      ]
    });
  }



    handleFormPostSubmission(
        "maidDeductionForm",
        "/update-advance",
        "#payrollTable",
        "#maid-dedction"
    );
 

    handleFormPostSubmission(
        "addMaidDeductionForm",
        "/store-new",
        "#payrollTable",
        "#add-maid-deduction-modal"
    );



    $("#selectAllMaids").on("click", function () {
        let isChecked = $(this).is(":checked");
        $(".maid-checkbox").prop("checked", isChecked);
    });
    $(document).on("click", ".maid-checkbox", function () {
        let totalCheckboxes = $(".maid-checkbox").length;
        let checkedCheckboxes = $(".maid-checkbox:checked").length;

        $("#selectAllMaids").prop(
            "checked",
            totalCheckboxes === checkedCheckboxes
        );
    });

  $("#bulkSaveButton").click(function () {
  const selectedMonth = $("#month").val();
  if (!selectedMonth) {
    alert("Please select a month.");
    return;
  }

  const bulkNote = $("#bulk-note").val() || "";

  // Use 25th of the selected month
  const end = new Date(`${selectedMonth}-01`);
  end.setDate(25);
  const formattedEndDate = end.toISOString().split("T")[0];

  const selectedMaids = $(".maid-checkbox:checked")
    .map(function () {
      const $row = $(this).closest("tr");
      const d = this.dataset;

      // prefer select value, fall back to data-method, default to cash
      const method =
        ($row.find("select.payment-select").val() || d.method || "cash").toLowerCase();

      // build the exact payload the controller expects
      return {
        maid_id: Number(d.maidId || this.value),      // ← KEY CHANGE
        date: formattedEndDate,
        type: d.type || "Unknown",
        totalDays: Number(d.totaldays) || 0,
        deduction: Number(d.deduction) || 0,
        allowance: Number(d.allowance) || 0,
        note: ((d.note ?? "No Note") + "__:" + bulkNote).trim(), 
        salary: Number(d.salary) || 0,
        net: Number(d.net) || 0,
        method: method,
        status: d.status || "Pending",
      };
    })
    .get();

  if (!selectedMaids.length) {
    alert("No maids selected for bulk payment.");
    return;
  }

  $.ajax({
    url: "/store-payroll",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({ maids: selectedMaids }),
    headers: {
      "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
    success: function (response) {
      alert(response.message || "Payrolls generated successfully.");
      if (typeof table !== "undefined" && table) {
        table.ajax.reload(null, false);
      }
    },
    error: function (xhr, status, error) {
      const msg = (xhr.responseJSON && xhr.responseJSON.error) || error || "Request failed.";
      alert("Error: " + msg);
    },
  });
});



  $(document).on("click", ".open-modal-btn", function () {
    const deduction = $(this).data("deduction");
    const allowance = $(this).data("allowance");
    const note      = $(this).data("note");
    const id        = $(this).data("id");
    const maidName  = $(this).data("maid_name"); 
    const maidId    = $(this).data("maid_id");   

    $("#maidNameForDeduction").val(maidName);   
    $("#maidIdForDeduction").val(maidId);       

    $("#deductionInput").val(deduction);
    $("#allowanceInput").val(allowance);
    $("#noteInput").val(note);
    $("#idForDeduction").val(id);               


  });

  // Add new record
  $(document).on("click", ".open-new-btn", function () {
    const maidName = $(this).data("maid_name"); 
    const maidId   = $(this).data("maid_id");   

    $("#maidNameAdd").val(maidName);           
    $("#maidIdAdd").val(maidId);               

    const selectedMonth = $("#month").val();
    $("#modalMonth").val(selectedMonth);        

    $("#deductionInputAdd").val('');
    $("#allowanceInputAdd").val('');
    $("#noteInputAdd").val('');


  });




});
