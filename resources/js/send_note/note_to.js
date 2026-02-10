
import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { display_table } from "../reuseable/display_table";
import { selectServerSideSearch } from "../reuseable/server_side_search";


$(document).ready(function () {


    display_table('/all/notified/complaints', '#notification_table',[
        {data:"assigned_to"},
        {data:"status"},
        {data:'contract_ref'},
        {
            data: "customer_name",
            render: function (data, type, row) {
                return `<a href="/customer/report/${row.customer_name}" target='_blank' >${row.customer_name}</a>`;
            },
        },
        {
            data: "maid_name",
            render: function (data, type, row) {
                return `<a href="/maid-report/${row.maid_name}" target='_blank' >${row.maid_name}</a>`;
            },
        },
        {data:"memo"},
        {data:"type"},
        {
            data: "created_at",
            render: function (data) {
                return data ? new Date(data).toLocaleDateString("en-CA") : "";
            },
        },
        {data:"created_by"},
        {data:"action_taken"},
        {data:"action"}

   
    ]);

    selectServerSideSearch(
        "#maidName",
        "/all/maids",
        "#complaintModal",
        "search maid"
    );
    
    selectServerSideSearch(
        "#customerName",
        "/all-customers",
        "#complaintModal",
        "search ledger"
    );


    selectServerSideSearch(
        "#assignedTo",
        "/searching-user",
        "#complaintModal",
        "search customer"
    );

    handleFormPostSubmission(
        "formComplaint",
        "/post/accounting-complain",
        "#notification_table",
        "#complaintModal"
    );

    handleFormPostSubmission(
        "updateNotify",
        "/update/notify",
        "#notification_table",
        "#updatecomplaintModal"
    );


    $(document).on("click", ".edit-notify-btn", function () {
        let id = $(this).data("id");
    
        $.ajax({
            url: "/get/notify/" + id,
            type: "GET",
            success: function (data) {

                $("#updatecomplaintModal").modal("show");
                
                selectServerSideSearch(
                    "#updatecustomerName",
                    "/all-customers",
                    "#updatecomplaintModal",
                    "search customer"
                );

                selectServerSideSearch(
                    "#updatemaidName",
                    "/all/maids",
                    "#updatecomplaintModal",
                    "search maid"
                );
            
                selectServerSideSearch(
                    "#updateassignedTo",
                    "/searching-user",
                    "#updatecomplaintModal",
                    "search staff"
                );

         
                $('#updateId').val(data?.id)
                 
                $("#updatememo").val(data?.memo)

                $("#statu").val(data?.status)
            
            
            },
            error: function (xhr) {
                alert("Error: " + xhr.responseJSON.message);
            },
        });
    });


    $(document).on("click", ".delete-notify-btn", function () {
        let id = $(this).data("id");
        let url = "/delete/notify/" + id;
      
        const confirm = window.confirm("Are you sure you want to delete this record?");
        if (confirm) {
            $.ajax({
                url: url,
                type: "DELETE",
                success: function (data) {
                    alert(data.message);
                    $("#notification_table").DataTable().ajax.reload();
                },
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                error: function (xhr) {
                    alert("Error: " + xhr.responseJSON.message);
                },
            });
        }
    });
});