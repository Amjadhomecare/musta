
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment Service Agreement</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 0;
        }
        .a4 {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 10mm auto;
            background: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        h1, h2, h3 {
            color: #333;
        }
        p {
            font-size: 14px;
            color: #666;
        }
        ul {
            margin-left: 20px;
            padding-left: 0;
        }
        li {
            margin: 10px 0;
        }
        @media print {
            body {
                margin: 0;
                height: auto;
            }
            .a4 {
                box-shadow: none;
                margin: 0;
                width: auto;
                height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="a4">

    
        <h1>Intermediate Agreement for Recruiting Services</h1>
        <p>This <strong>Recruitment Service Agreement</strong> is signed and entered by and between:</p>

        <h2>FIRST PARTY:</h2>
        <p><strong>{{env('company_email') ?? "NA" }} </strong>, a business firm under the laws of Dubai, U.A.E, with registered Trade License Number 796693, located at PO Box 36067, 342-Jumeirah Second, DUBAI, UAE represented by its Owner, hereinafter referred to as the <strong>FIRST PARTY</strong>;</p>

        <h2>SECOND PARTY:</h2>
        <p><strong> {{$con?->maidInfo?->nationality}} Agent</strong>, ______{{$con?->maidInfo?->agency}}____ with office address at ____{{$con?->maidInfo?->nationality}}___, hereinafter referred to as the <strong>International agent</strong></p>

        <h2>THIRD PARTY:</h2>
        <p>The customer …{{$con?->customerInfo?->name}} … who are seeking to get domestic worker directly from the international agent herein after referred to as Customer</p>

        <h3>Recitals</h3>
        <p>Whereas, the first party offers the customers comprehensive services related to recruiting foreign domestic workers according to the rules and regulations stipulated by the Ministry.</p>
        <p>Whereas, the second party works outside the UAE in the territories of………{{$con?->maidInfo?->nationality}} ……..has the experience and knowledge to provide workers upon request</p>
        <p>All parties have agreed on the following:</p>

        <ul>
            <li>The above preamble and all the foregoing clauses are considered an integrated part of this agreement.</li>
            <li>The first party shall contact the second party once they have an inquiry from the third customer to get a foreign domestic worker.</li>
            <li>The second party shall respond to the request of the third party, and shall charge him directly based on a pre-agreed list of prices.</li>
            <li>The payment terms have been agreed to be as follows:</li>
                <ul>
                    <li>The customer to pay directly to the second party, based on an invoice issued directly from the second party to the customer, both parties have agreed that in this case the first party is not responsible for any loss or unpaid amounts made directly between him and the customer.</li>
                    <li>The customer to pay full amount to the first party, in this case the first party obliged to issue invoice showing in details the amount dues to international agent and other amount related to the direct supply and services provided by the first party to the customer.</li>
                    <li>The first party shall transfer to the second party all the amounts received by the customer with a maximum of 7 days from the date of received.</li>
                    <li>The second party agreed to give a guarantee three months to the customer to replace the worker or to take it back.</li>
                    <li>The second party agreed to refund the received amount to the customer in case of replacement or cancellation within the three months guarantee.</li>
                </ul>
            <li>The first party eligible to provide services and get commission from the customer to facilitate the supply through the second party, therefore the second party shall not contact directly the customer to offer him direct supply without notifying the first party.</li>
            <li>The first party shall be responsible to arrange the visa to the works under the first party sponsorship or directly under the customer visa depend on the agreement case by case</li>
            <li>In case of disputes arising from the implementation of the agreement between the first party and the second party, all effort shall be made to settle them amicably.</li>
            <li>In case the amicable settlement fails, the matter shall be submitted to the Dubai Court in the United Arab Emirates.</li>
            <li>This agreement shall be the law between both parties and shall be valid for two (2) years unless otherwise terminated by written notification of either partier. Otherwise, it shall be renewed automatically with mutual understanding of both parties.</li>
        </ul>

        <p>IN WITNESS WHEREOF, we have hereunto set our hands, this ______________________, at Dubai, United Arab Emirates.</p>

        <h3>Signatories:</h3>
    >
        <p>TADBEER HOMECARE FOR DOMESTIC WORKERS SERVICES</p>

        <p>Second Party: {{$con?->customer}} </p>
        <p><img src="{{$con?->signature}}" style="width: 50%; max-width: 120px;"></p>

        <p>Third Party:{{$con?->maidInfo?->agency}}</p>
    </div>
</body>
</html>
