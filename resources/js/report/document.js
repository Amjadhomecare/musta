import { display_table } from "../reuseable/display_table";
import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { ajaxSelector } from "../ajax_select2";

$(document).ready(function () {
    display_table("/upload-document", "#doc_datatable", [

          {
            data: "created_at",
            title: "Created At",
            render: (data, type) => {
                if (!data) return "";
                if (type === "display" || type === "filter") {
                    const d = new Date(data);
                    return d.toLocaleDateString("en-GB", {
                        day:   "2-digit",
                        month: "short",
                        year:  "numeric",
                    }) +
                    " " +
                    d.toLocaleTimeString("en-GB", {
                        hour:   "2-digit",
                        minute: "2-digit",
                    });
                }
                return data;
            }
        },

        { data: "person", title: "Person" },

        // View link
        {
            data: "s3_url",
            title: "Document",
            render: (data, type) =>
                (type === "display" && data)
                    ? `<a href="${data}" target="_blank" rel="noopener">View</a>`
                    : data
        },

        // Expire Date → e.g. 15 May 2025
        {
            data: "expire_date",
            title: "Expire Date",
            render: (data, type) => {
                if (!data) return "";
                if (type === "display" || type === "filter") {
                    const d = new Date(data);
                    return d.toLocaleDateString("en-GB", {
                        day:   "2-digit",
                        month: "short",
                        year:  "numeric",
                    });
                }
                return data;   // keep ISO for sorting
            }
        },

        { data: "note", title: "Note" },

        {
            data: 'id',
            orderable: false,
            searchable: false,
            render: id => `
                <button class="btn btn-sm btn-danger delete-doc"
                        data-id="${id}">
                    <i class="bi bi-trash"></i>
                </button>`
        },
      

       
    ]);


    ajaxSelector(
        "#personSelect",          
        "/searching-user",         
        "Search person…",           
        "#documentModal"           
  
    );

   
    handleFormPostSubmission(
        "documentForm",
        "/upload-document/store",
        "#doc_datatable",
        "#documentModal",
    );


});



$(document).on('click', '.delete-doc', function () {
    const id     = $(this).data('id');
    const token  = $('meta[name="csrf-token"]').attr('content');
    const url    = '/upload-document/delete';   // Ziggy helper; or '/upload-document/delete'

    Swal.fire({
        title: 'Are you sure?',
        text:  "You won't be able to revert this!",
        icon:  'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor:  '#d33',
        confirmButtonText:  'Yes, delete it!'
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(url, {
            method:  'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id })
        })
        .then(res => res.json())
        .then(data => {
            Swal.fire({
                title: data.success ? 'Deleted!' : 'Error!',
                text:  data.message,
                icon:  data.success ? 'success' : 'error'
            });
            if (data.success) {
                $('#doc_datatable').DataTable().ajax.reload(null, false);
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error!', 'Failed to delete the document.', 'error');
        });
    });
});