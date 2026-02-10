document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function(e){
        if(e.target && e.target.id === 'delete'){
            e.preventDefault();
            var link = e.target.getAttribute('href');

            // SweetAlert2: Show the confirmation dialog
            Swal.fire({
                title: 'Are you sure?',
                text: 'Delete This Data?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link;
                    Swal.fire(
                        'Deleted!',
                        'Your file has been deleted.',
                        'success'
                    );
                }
            });
        }
    });
});
