<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>Receipt Voucher</title>
    <style>
        body {
            background-color: #f4f4f4;
            font-family: 'Arial', sans-serif;
        }
        .receipt-container {
            max-width: 700px;
            margin: 2rem auto;
            padding: 2rem;
            border: 2px solid #333;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .header h2 {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .header p {
            margin: 0;
        }
        .receipt-details {
            margin-bottom: 1.5rem;
        }
        .receipt-details p {
            font-size: 1.1rem;
            margin: 0;
            padding: 0.3rem 0;
        }
        .amount-box {
            text-align: center;
            margin: 1.5rem 0;
            padding: 1rem;
            border: 2px solid #333;
            font-size: 1.5rem;
            font-weight: bold;
            color: #d9534f;
            background-color: #f9f9f9;
        }
        .table th, .table td {
            vertical-align: middle;
            border: 1px solid #333;
            padding: 0.75rem;
            text-align: left;
        }
        .table th {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.9rem;
            color: #777;
        }
        .barcode {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }
        .print-btn {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
        }
        .no-print {
            display: none;
        }
        @media print {
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="container receipt-container">
        <!-- Header -->
        <div class="header">
            <h2>Receipt Voucher</h2>
            <img style="width: 90px; height: 90px; padding-right: 20px;" class="logo" src=" {{ env('logo') }}" alt="">
            <p><strong> {{env('company_name') ?? "NA" }}</strong></p>
            <p>Ref No: <strong>{{$debitData->refCode ?? 'NA' }}</strong></p>
        </div>



        <!-- Receipt Details -->
        <div class="receipt-details">
            <p><strong>Date:</strong> {{$debitData->date ?? 'NA' }}</p>
            <p><strong>Received From:</strong> {{ $creditData->accountLedger->ledger ?? 'NA' }}</p>
            <p><strong>Received By:</strong> {{$debitData->created_by ?? 'NA' }}</p>
            <p><strong>Payment Mode:</strong> {{$debitData->accountLedger->ledger ?? 'NA' }} </p>
            <p><strong>For:</strong> {{$relatedReceiveData->pre_connection_name ?? 'NA'}}</p>
        </div>

        <!-- Amount Box -->
        <div class="amount-box">
            AED {{$debitData->amount ?? 'NA' }}
        </div>



        <!-- Signature Section -->
   <!-- Signature Section -->
    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>Notes</th>
                <th>Maid</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="height: 80px;">{{ $debitData->notes ?? 'NA' }}</td>
                <td style="height: 80px;">{{ $debitData->maidRelation->name ?? 'NA' }}</td>
            </tr>
        </tbody>
    </table>


        <!-- Barcode -->
        <div class="barcode">
         <img src="https://barcode.tec-it.com/barcode.ashx?data={{ $debitData->refCode ?? 'NA' }}&code=QRCode"  alt="QR Code">

        </div>

        <!-- Print Button -->
        <div class="print-btn">
            <button class="btn btn-primary" onclick="window.print();">Print Receipt</button>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is a system-generated receipt and does not require a signature.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
