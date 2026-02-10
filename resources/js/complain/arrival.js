import { display_table } from "../reuseable/display_table";



$(document).ready(function() {


    const columns = [
        { data: "maid_name" },
        { data: "nationality" },
        { data: "agent" },
        { data: "note" },
        { data: "status" },
        { data: "created_by" },
        { data: "updated_by" },
        { data: "created_at" },
        {
            data: "actions",
    
            orderable: false,
            searchable: false,
        },
    ];

    display_table("/arrival-list", "#arrival_table", columns);



    $('#arrival_table').on('click', '#delete-arrival', function(e) {
        e.preventDefault();
        
        const id = $(this).data('id');
        const url = `/delete-arrival/${id}`;

  
        if (confirm('Are you sure you want to delete this arrival?')) {
          
            $.ajax({
                url: url,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') 
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message); 
                    
                        $("#arrival_table").DataTable().ajax.reload(null, false);
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error); 
                    alert('Something went wrong! Please try again.');
                }
            });
        }
    });



$('#maid_id').select2({
  placeholder: 'Search for a maid',
  minimumInputLength: 1,
  ajax: {
    url: '/all/maids',
    dataType: 'json',
    delay: 250,
    data: params => ({
      search: params.term || '',
      page: params.page || 1
    }),
    processResults: function (data) {

      const results = data.items.map(item => ({
        id: item.system_id,   
        text: item.text
      }));
      return {
        results,
        pagination: { more: data.total_count > results.length }
      };
    },
    cache: true
  }
}).on('select2:select', function (e) {
  const selectedId = e.params.data.id; 
  fetch('/ajax-maid/' + selectedId)
    .then(r => r.json())
    .then(data => {
      $('#nationality').val(data.nationality || '');
      $('#agent').val(data.agent || '');
    })
    .catch(err => {
      console.error(err);
      alert('Failed to load maid info.');
    });
});



});