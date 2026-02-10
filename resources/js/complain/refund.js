        
    let dataTable = $('#credit-memo-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "/ajax-credit-memo-list",
            columns: [

                { data:'date', data:'date' },  
                { data:'memo_ref ', data:'memo_ref' },  
                { data:'contract_ref', data:'contract_ref' },  
                { data:'contract_type', data:'contract_type' },  
                { data:'customer', data:'customer' },  
                { data:'maid', data:'maid' },  
                { data:'note', data:'note' }, 
                { data:'started_date', data:'started_date' },  
                { data:'returned_date', data:'returned_date' },
                { data:'refunded_amount', data:'refunded_amount' },
                {
                    data: 'id',
                    render: function(data, type, row) {
                
                        return `<a href="/pdf-credit-memo-list/${row.id}" target="_blank" >PDF</a>`;
                    }
                }

                ] ,
                order: [[0, 'desc']], 
                dom: 'Blfrtip', 
                buttons: [
                    'csv', 'excel', 'pdf', 'print'
                ],
                pagingType: 'full_numbers',
                pageLength: 10
            
                }); // end


document.addEventListener('DOMContentLoaded', function () {
 
    const contractRefSelect = document.getElementById('contract_ref');

    contractRefSelect.addEventListener('change', function () {
        const contractRef = this.value;

        fetch('/ajax-cat1/' + contractRef)
            .then(response => {
                if (response.ok) {
                    return response.json();
                } else {
                  
                    throw new Error('contract should have a return action Server responded with status ' + response.status);
                }
            })
            .then(data => {
                document.getElementById('thecustomer').value = data.customer;
                document.getElementById('themaid').value = data.maid;
                document.getElementById('thecategory').value = data.type;
                document.getElementById('thestarted_date').value = data.started_date;

              
                if (data.return_date) {
                    document.getElementById('thereturned_date').value = data.return_date;
                } else {

                    alert('This contract should have a return date.');
                    document.getElementById('thereturned_date').value = ''; 
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
            });
    });

});

document.addEventListener('DOMContentLoaded', function () {
 
 const contractRefSelect = document.getElementById('contract_ref4');

 contractRefSelect.addEventListener('change', function () {
     const contractRef = this.value;

     fetch('/ajax-cat4/' + contractRef)
         .then(response => {
             if (response.ok) {
                 return response.json();
             } else {
               
                 throw new Error('contract should have a return action Server responded with status ' + response.status);
             }
         })
         .then(data => {
             document.getElementById('thecustomer').value = data.customer;
             document.getElementById('themaid').value = data.maid;
             document.getElementById('thecategory').value = data.type;
             document.getElementById('thestarted_date').value = data.started_date;

           
             if (data.return_date) {
                 document.getElementById('thereturned_date').value = data.return_date;
             } else {

                 alert('This contract should have a return date.');
                 document.getElementById('thereturned_date').value = ''; 
             }
         })
         .catch(error => {
             console.error('Error:', error);
             alert('An error occurred: ' + error.message);
         });
 });

});


document.addEventListener('DOMContentLoaded', () => {
    const amountReceived = document.getElementById('amount_received');
    const amountDeduction = document.getElementById('amount_deduction');
    const refundedAmount = document.getElementById('refunded_amount');
    const amountSalary = document.getElementById('amount_salary');

    const calculateRefundedAmount = () => {
        const receivedValue = parseFloat(amountReceived.value) || 0;
        const deductionValue = parseFloat(amountDeduction.value) || 0;
        const salaryValue = parseFloat(amountSalary.value) || 0;

        refundedAmount.value = receivedValue - deductionValue - salaryValue;
    };

    amountDeduction.addEventListener('input', calculateRefundedAmount);
    amountReceived.addEventListener('input', calculateRefundedAmount);
    amountSalary.addEventListener('input', calculateRefundedAmount);
});




document.addEventListener('DOMContentLoaded', (event) => {
    document.getElementById('creditMemoForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        fetch("/ajax-store-credit-memo", {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
               alert('saved successfully');

               $('#credit-memo-form-modal').modal('hide');      
                dataTable.ajax.reload(null, false);
            } else {
                
                let errorMessage = 'Something went wrong:\n';
                if (data.errors) {
                    for (const key in data.errors) {
                        errorMessage += `${key}: ${data.errors[key].join(', ')}\n`;
                    }
                }
                alert(errorMessage);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected error occurred');
        });
    });
});
  
 