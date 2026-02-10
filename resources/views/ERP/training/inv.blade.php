<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #fff; }
        .invoice-box {
            width: 210mm;
            min-height: 297mm;
            padding: 32px 40px;
            margin: auto;
            background: #fff;
            border: 1px solid #eee;
            box-sizing: border-box;
        }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .company {
            font-size: 20px;
            font-weight: bold;
        }
        .company-details {
            font-size: 13px;
            color: #555;
            margin-top: 5px;
        }
        h2 { margin-bottom: 0; }
        .meta { margin-top: 16px; }
        .meta strong { display: inline-block; width: 150px; }
        table { width: 100%; border-collapse: collapse; margin-top: 32px; }
        th, td { border: 1px solid #ddd; padding: 14px 10px; text-align: left; }
        .footer { margin-top: 50px; text-align: center; color: #888; font-size: 13px; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .invoice-box { border: none; box-shadow: none; }
        }
    </style>
</head>
<body>
<div class="invoice-box">
    <div class="header">
        <div class="company">
            {{ $company['name'] }}
            <div class="company-details">
                {{ $company['address'] }}<br>
                Tel: {{ $company['phone'] }}<br>
                Email: {{ $company['email'] }}
            </div>
        </div>
        {{-- <div class="logo">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" height="60">
        </div> --}}
    </div>

    <hr style="margin-bottom: 28px;">

    <h2>Training Center Invoice</h2>

    <div class="meta">
        <div><strong>Invoice #:</strong> {{ $invoice->id }}</div>
        <div><strong>Date:</strong> {{ \Carbon\Carbon::parse($invoice->date)->format('d/m/Y') }}</div>
        <div><strong>Customer Name:</strong> {{ $invoice->customer_name ?? $invoice->customerName }}</div>
        <div><strong>Maid Name:</strong> {{ $invoice->maid_name ?? $invoice->maidName }}</div>
        @if($invoice->ref ?? $invoice->ref)
        <div><strong>Reference:</strong> {{ $invoice->ref }}</div>
        @endif
        @if($invoice->branch ?? $invoice->branch)
        <div><strong>Branch:</strong> {{ $invoice->branch }}</div>
        @endif
    </div>

    <table>
        <tr>
            <th>Description</th>
            <th>Amount (AED)</th>
        </tr>
        <tr>
            <td>
                Training Invoice @if($invoice->ref ?? $invoice->ref)(Ref: {{ $invoice->ref }})@endif
            </td>
            <td>{{ number_format($invoice->amount, 2) }}</td>
        </tr>
    </table>

    <div style="margin-top:32px; text-align:right;">
        <h3>Total: AED {{ number_format($invoice->amount, 2) }}</h3>
    </div>

    <div class="footer">
        {{ $company['name'] }} &ndash; {{ $company['address'] }}<br>
        Tel: {{ $company['phone'] }} | Email: {{ $company['email'] }}
    </div>
    <div class="no-print" style="margin-top:30px;text-align:center;">
        <button onclick="window.print()">Print Invoice</button>
    </div>
</div>
</body>
</html>
