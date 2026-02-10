@extends('keen')
@section('content')



<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

<style>
  @media screen {
    .invoice-page {
      width: 210mm;
      min-height: 297mm;
      padding: 20mm;
      margin: auto;
      background: white;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
  }

  @media print {
    .invoice-page {
      width: 210mm;
      height: 297mm;
      padding: 20mm;
      box-shadow: none;
    }
    .no-print {
      display: none;
    }
  }
</style>

<div class="invoice-page">
    <div class="card">
        <div class="card-content">
            <span class="card-title blue-text">Invoice {{$typing_invoice->last()->voucher_type}}</span>
            <p class="grey-text">Date: {{$typing_invoice[0]->date}}</p>
            <div class="section">
                <h5>Customer Details</h5>
                <p>Customer: {{$typing_invoice->last()->account}}</p>
            </div>
            <table class="highlight responsive-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Notes</th>
                        <th>Amount</th>               
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $typing_invoice->last()['pre_connection_name']}}</td>
                        <td>{{ $typing_invoice->last()['Notes']}}</td>
                        <td>{{ $typing_invoice->last()['amount']}}</td>
                    </tr>                
                </tbody>
            </table>
        </div>
        <div class="card-action center-align no-print">
            <a class="waves-effect waves-light btn">Download Invoice</a>
            <p class="grey-text">Thank you for your business!</p>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    JsBarcode("#barcodeCanvas", "{{$typing_invoice->last()->voucher_type}}");
});
</script>

@endsection
