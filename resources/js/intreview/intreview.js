import { display_table } from "../reuseable/display_table";
import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { ajaxSelector } from "../ajax_select2";


document.addEventListener("DOMContentLoaded", () => { 

  display_table("/table-interview", "#table-interview", [
    {
        data: "maid_name",
        title: "Maid Name",
        render: function (data, type, row) {
            return `<a href="/maid-report/${encodeURIComponent(row.maid_name)}  " target='__blank' class="text-primary">${data}</a>`;
        }
    },
    {
        data: "customer_name",
        title: "Customer Name",
        render: function (data, type, row) {
            return `<a href="/customer/make/p4/${encodeURIComponent(row.customer_name)}"  target="__blank" class="text-primary">${data}</a>`;
        }
    },
    { data: "note", title: "Note" },
    {
        data: "status",
        title: "Status",
        render: function (data) {
            switch (data) {
                case 0: return `<span class="badge bg-warning">Pending</span>`;
                case 1: return `<span class="badge bg-success">Success</span>`;
                case 2: return `<span class="badge bg-danger">Maid Rejected</span>`;
                case 3: return `<span class="badge bg-info">Customer Rejected</span>`;
                default: return `<span class="badge bg-secondary">Unknown</span>`;
            }
        }
    },
    { data: "type", title: "Maid Type" },
    { data: "room", title: "Room" },
    {
        data: "created_by",
        title: "Created By"
    },
    {
        data: "updated_by",
        title: "Updated By"
    },
    {
        data: "created_at",
        title: "Created At",
        render: function (data) {
            return moment(data).format("YYYY-MM-DD HH:mm:ss");
        }
    },
    {
        data: "updated_at",
        title: "Updated At",
        render: function (data) {
            return moment(data).format("YYYY-MM-DD HH:mm:ss");
        }
    },
    {
        data: "actions",
        title: "Action",
        orderable: false,
        searchable: false
    }
]);


function applyFilters() {
    let statusFilter = $("#status-id").val();

    $("#table-interview")
        .DataTable()
        .column(3)
        .search(statusFilter)
        .draw();
}

$("#status-id").on("change", applyFilters);

        handleFormPostSubmission('interview-form' ,'/store-interview' , '#table-interview' , '#add-modal' ) 
        handleFormPostSubmission('edit-interview-form' ,'/update-interview' , '#table-interview' , '#edit-modal' )


        $('#add-modal').on('shown.bs.modal', function () {

          if (!$.fn.select2 || $('#maid_name').data('select2')) {
              return;
          }
          ajaxSelector("#maid_name", "/all/maids", "Search Maid...", "#add-modal");
          ajaxSelector("#customer_name", "/all-customers", "Search Customer...", "#add-modal");
  
          setTimeout(() => {
              $('#maid_name').select2('open');
          }, 300);
      });


      $('#edit-modal').on('shown.bs.modal', function () {
        ajaxSelector("#edit_maid_name", "/all/maids", "Search Maid...", "#edit-modal");
        ajaxSelector("#edit_customer_name", "/all-customers", "Search Customer...", "#edit-modal");
    });

    $('#table-interview').on('click', '.edit-btn', function () {
        let interviewId = $(this).data('id');
        $.ajax({
            url: `/interview/${interviewId}`,
            method: "GET",
            success: function (response) {
                $('#edit_interview_id').val(interviewId);
                $('#edit_status').val(response.status);
                $('#edit_note').val(response.note);
                $('#edit_room').val(response.room);

               
                setTimeout(() => {
                    $('#edit_maid_name').append(new Option(response.maid_name, response.maid_name, true, true)).trigger('change');
                    $('#edit_customer_name').append(new Option(response.customer_name, response.customer_name, true, true)).trigger('change');
                }, 500);

                $('#edit-modal').modal('show');
            },
            error: function () {
                alert("Failed to fetch interview details.");
            }
        });
    });

        
});
