<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Letter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        p {
            line-height: 1.6;
        }
        .signature {
            margin-top: 30px;
        }
        .client-details {
            margin-top: 20px;
        }
        .client-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .client-details td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .signature p {
            margin: 0;
        }
        .signature-line {
            margin-top: 50px;
        }
    </style>
</head>
<body>


<div class="container">

    <h2>Transfer Letter</h2>

    <img
style='height: 100px;'
  src="{{env('logo')}}"
  alt="" />

    <p>To whom it may concern,</p>

    <p>This is to certify and confirm that I, <strong>MR/Ms: {{$conDetails?->customerInfo->name ?? ""}}</strong>, a {{$conDetails?->customerInfo?->nationality}} national with EMIRATES ID NO: <strong>{{$conDetails?->customerInfo?->idNumber}}</strong>, am taking the {{$conDetails?->maidInfo?->nationality}} housemaid <strong>MS. {{$conDetails?->maidInfo->name}}</strong> for housemaid service at my residence/villa starting from <strong>{{$conDetails?->created_at}}</strong>.</p>

    <p>I am aware that in the event of terminating my contract before the end of a month, the company shall return/refund the amount with an AED 150 deduction for every day the housemaid has worked with me during that month.
</p>

    <p>I am also aware that in the event of late payment, if 10 days after the due date I still have not paid or the company hasn’t received any updates from me, the company reserves the right to request the worker to return to the office.</p>




    <p>
        
   Monthly payments will be automatically deducted from the provided bank account using credit card/direct debit.
    Additionally, I acknowledge that the maid just recently arrived in the UAE, and {{env('company_name')}} is handling the processing of her visa.
</p>

    <div class="client-details">
        <h3>Client Details</h3>
        <table>
            <tr>
                <td><strong>MR/Ms:</strong></td>
                <td>{{$conDetails?->customerInfo?->name}}</td>
            </tr>
            <tr>
                <td><strong>EMIRATES ID NO:</strong></td>
                <td>{{$conDetails?->customerInfo?->idNumber}}</td>
            </tr>
            <tr>
                <td><strong>MOBILE NO:</strong></td>
                <td>{{$conDetails?->customerInfo?->phone}} / {{$conDetails?->customerInfo?->secondaryPhone }}</td>


            </tr>
        </table>
    </div>

    <div class="signature">
        <p>Signature:</p>
        <div class="signature-line"><img src="{{$conDetails?->signature}}" style="width: 50%; max-width: 200px;"></td></div>
    </div>
</div>

</body>
</html>
