<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Contract</title>
  
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
  <link rel="stylesheet"  type="text/css" media="print">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>

<style>
/* Remove the navbar's default margin-bottom and rounded borders */ 
@page {
size: A4;
margin: 0;
}
@media print {
html, body {
width: 210mm;
height: 297mm;
}
/* ... the rest of the rules ... */
}
table {
boredr:1px solid;
}
.bordertop {
border-top: 1px solid !important;
}
 table .tr_head { background: #B68834;color: #FFF;font-weight: bold; }
</style>
  <script>
function printContent(el){
var restorepage = $('body').html();
var printcontent = $('#' + el).clone();
$('body').empty().html(printcontent);
window.print();
$('body').html(restorepage);
}
</script>
</head>
<body>


<div class="container">
  <div class="row" style="width: 210mm;border: 1px solid;text-align: center;margin-left: auto;margin-right: auto;">
    <a href="/get/arabic-p4/{{$con4?->id}}" class="btn btn-xs btn-danger no-print pull-right" style="width: 100%;margin-top: 10px;margin-bottom: 10px;">
      <i class="fa fa-print"></i> Arabic View    </a>
  
  <button type="button" class="btn btn-xs btn-default no-print pull-right" style="width: 100%;background: #dbdbdb;" onclick="printContent('print');">
    <i class="fa fa-print"></i> Print  </button>
  </div>
</div>
<div class="container" style="width: 210mm" id="print">  

  <div class="row" style="border: 1px solid">
      <table class="table" style="margin-top: 2px;">
          <tbody><tr class="tr_head">
            <td align="center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/mohregg.jpg">
            </td>
          </tr>
      </tbody></table>

      <table class="table">
          <tbody><tr class="tr_head">
            <td align="center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p4/pkg4_4.png">
            </td>
          </tr>
      </tbody></table>

      <table class="table" border="1">
          <tbody><tr>
            <td align="left" class="bordertop" colspan="3">
              Contract Number: {{$con4?->Contract_ref}}         </td>
          </tr>

          <tr>
       
            <td align="left" class="bordertop">The Date : {{$con4?->created_at}}</td>
            <td align="left" class="bordertop">The Emirate : {{env('company_emirate') ?? "Dubai" }}</td>
          </tr>
          <tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop" colspan="3">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p4/pkg1_2.png">
            </td>
          </tr>
      </tbody></table>

      <table class="table" style="margin-top: -21px;width: 5%;float: left;" border="1">
          
          <tbody><tr>
            <td align="left" class="bordertop" style="background: #b68a35">
            <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p4/table.png" style="height: 86%;"></td>
          </tr>
      </tbody></table>

      <table class="table" style="margin-top: -21px;width: 94%;float: left;position: relative;left: 6px;" border="1">
        <tbody><tr style="border-left: 1px solid #FFF;">
          <td colspan="2" class="bordertop" style="border-left: #FFF;">{{env('company_name') ?? "NA" }}</td>
        </tr>

        <tr style="border-left: 1px solid #FFF;">
          <td colspan="2" class="bordertop" style="border-left: #FFF;">Represented by : {{$con4?->created_by}}</td>
        </tr>

        <tr style="border-left: 1px solid #FFF;">
 
          <td class="bordertop" width="50%" style="border-left: #FFF;">License No.: {{env('company_License_no') ?? "NA" }} </td>
        </tr>

        <tr style="border-left: 1px solid #FFF;">
          
          <td class="bordertop" width="50%" style="border-left: #FFF;">Mobile:{{env('company_phone') ?? "NA" }}  </td>
        </tr>

        <tr style="border-left: 1px solid #FFF;">
          <td class="bordertop" width="50%" style="border-left: #FFF;">P.O box : {{env('company_po_box') ?? "NA" }} </td>
          <td class="bordertop" width="50%" style="border-left: #FFF;">E-mail : {{env('company_email') ?? "NA" }} </td>
        </tr>

        <tr style="border-left: 1px solid #FFF;">
          <td colspan="2" class="bordertop" style="border-left: #FFF;">Address (Workplace): Dubai</td>
        </tr>

        <tr style="border-left: 1px solid #FFF;">
          <td colspan="2" class="bordertop" style="border-left: #FFF;">Name: {{$con4?->customerInfo?->name}}</td>
        </tr>

        <tr style="border-left: 1px solid #FFF;">
          <td class="bordertop" width="50%" style="border-left: #FFF;">Gender: </td>
          <td class="bordertop" width="50%" style="border-left: #FFF;">Nationality: {{$con4?->customerInfo?->nationality}}</td>
        </tr>

        <tr style="border-left: 1px solid #FFF;">
          <td class="bordertop" width="50%" style="border-left: #FFF;">Passport No.: </td>
          <td class="bordertop" width="50%" style="border-left: #FFF;">ID No.: {{$con4?->customerInfo?->idNumber}}</td>
        </tr>

        <tr style="border-left: 1px solid #FFF;">
          <td class="bordertop" width="50%" style="border-left: #FFF;">Email: {{$con4?->customerInfo?->email}}</td>
          <td class="bordertop" width="50%" style="border-left: #FFF;">Mobile: {{$con4?->customerInfo?->phone}}<</td>
        </tr>

        <tr style="border-left: 1px solid #FFF;">
          <td colspan="2" class="bordertop" style="border-left: #FFF;">Address: {{$con4?->customerInfo?->address}}</td>
        </tr>
      </tbody></table>

      <table class="table">
          <tbody><tr>
            <td align="center" style="font-weight: bold;border-top: none;">Preamble</td>
          </tr>
          <tr>
            <td align="left" style="border-top: none;">The Second Party wishes to hire a domestic worker; and accordingly approached the Second party to assign an Assistant to work under the First Party’s management and supervision. Having acknowledged their full eligibility to enter this contract, both parties agreed as follows :</td>
          </tr>
      </tbody></table>

      <table class="table" style="margin-top: 2px;">
          <tbody><tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p4/pkg2_2.png">
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
            The two parties agreed that the First Party shall provide a domestic worker through Flexi Service Package: (Hour/Day/Week/Month). and provide such service to the Second Party according to such assignment by the Second Party and within the scope of the Assistant’s profession as (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;) The worker details are listed below:
            </td>
          </tr>
      </tbody></table>


      <table class="table" border="1">
          <tbody><tr>
            <td class="bordertop" colspan="3">Name: {{$con4?->maidInfo?->name}}</td>
          </tr>

          <tr>
            <td align="left" class="bordertop" width="45%">Nationality :  {{$con4?->maidInfo?->nationality}}</td>
            <td align="left" class="bordertop">Gender : Female</td>
            <td align="left" class="bordertop">Age : {{$con4?->maidInfo?->age}}</td>

          </tr>

          <tr>
            <td class="bordertop" colspan="2">Languages: 
              English              -Arabic                                        </td>
            <td class="bordertop">Marital status: {{$con4?->maidInfo?->marital_status}}</td>
          </tr>
         <tr>
           <td class="bordertop" colspan="3">Skills:</td>
         </tr>
      </tbody></table>

      <table class="table" style="margin-top: 2px;">
          <tbody><tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p4/pkg2_3.png">
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">The term of this contract shall be (Hour/Day/Week/Month)commencing on( {{$con4?->created_at}} ) , subject to renewal for one or more similar terms, by virtue of a notice from the Second Party to the First Party before the end of the agreed term. Both parties agree that the Second Party will notify the First Party about their intentions to renew the contract ( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;) before it expires.</td>
          </tr>
      </tbody></table>

      <table class="table" style="margin-top: 2px;">
          <tbody><tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p4/pkg2_4.png">
            </td>
          </tr>
          <tr>
            <td align="left" style="border-top: none;font-weight: bold;">The Second Party agrees to:</td>
          </tr>
          <tr>
            <td align="left" style="border-top: none;">1. Pay the First Party the cost of the: hour/day/week/month in consideration for the provision of the services referred to above;according to the value of the hour/day/week/ month being ( AED {{$con4?->installmentInfo->last()?->amount}}), 
          </tr>

          <tr>
            <td align="left" style="border-top: none;">2. Pay the First Party for total amount of the actual hours/days/weeks/months in advance.</td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">3. Pay at the beginning of each month, and he shall also pay the monthly instalments equal to the contractual period, to the first party (in cash, cheque, or credit card), to be deducted on monthly basis.</td>
          </tr>

          
      </tbody></table>



      <table class="table" style="margin-top: 2px;">
          <tbody><tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p4/pkg2_5.png">
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;font-weight: bold;">The First Party is obligated to:</td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              1. ProvideThe services of domestic worker,as mentioned above, to the Second Party.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              2. Provide medical documents proving the worker’s physical fitness and his mental and occupational aptitude to perform the job.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              3. Pay the monthly salary of the worker in addition to other legal dues as prescribed.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              4. Fully ensure that the worker fulfills the conditions and requirements, as required by the legal regulations in force within the UAE, to practice a particular profession, job or particular duty.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              5. Provide a substitute worker with the same qualifications and expertise to perform the same job for which the domestic worker was requested, at the request of the Second Party at any time. The replacement can also be made in the case of the principal worker’s absenteeism or refusal to work, within 24 hours from the time the Second Party notifies the First Party.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              6. The First Party shall not replace the worker assigned, unless written approval was received from the Second Party.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              7. The Second Party has the right to have the worker replaced with another one when needed, and throughout the contractual period.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              8. The Second Party shall be compensated for any loss, damage or destruction of his property caused by the domestic worker, after being proven by the competent authorities.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              9. Provide all the requirements imposed by the legal systems in force in the Ministry of Human Resources and Emiratisation relating to the employer, unless the Parties agreed otherwise. In all cases, notwithstanding such contracts between the First and Second Parties; the First Party shall not be exempted from Liability in the event that the beneficiary fails to fulfill his or her obligation, and the abstaining beneficiary shall assume legal responsibility.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              10. Any other obligations imposed by the relevant legal regulations as applicable in the Ministry.
            </td>
          </tr>
      </tbody></table>


      <table class="table" style="margin-top: 2px;">
          <tbody><tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p4/pkg2_6.png">
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;font-weight: bold;">
              The Second Party is obligated to:
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              1. Pay the value agreed upon in clause three.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              2. Treat the worker in a good way that preserves his/ her dignity and wellbeing.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              3. Notify the First Party of any violations or errors committed by the worker to take the necessary action against him/her.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              4. The worker shall not be assigned to work in a profession that is different from the nature of his/her work, except upon his/her consent and provided that such profession is sanctioned by Law.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              5. Notify the First Party in the event of the worker’s abstention or refusal to work, within (24) hours of the abstention or refusal time, in addition to the delivery of all the worker's belongings to the First Party,
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              6. If the worker’s profession is a driver; the vehicle delivered to him/her shall have a valid vehicle insurance and license.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              7. Provide an adequate accommodation for the worker unless otherwise is agreed upon with the Second Party.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              8. Provide the worker means of sustenance such as meals and appropriate clothing for work performance, unless otherwise is agreed upon with the First Party.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              9. Provide the worker an adequate environment and work tools in accordance with the legal regulations in force in within the United Arab Emirates.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              10. The worker shall not work for a third parties.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              11. Provide the worker with all that he has been agreed with the First Party.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              12. Any other obligations prescribed by the relevant legal regulations followed by the Ministry.
            </td>
          </tr>

      </tbody></table>

      <table class="table" style="margin-top: 2px;">
          <tbody><tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p4/pkg4_1.png">
            </td>
          </tr>

          

          <tr>
            <td align="left" style="border-top: none;">
              1. No external agreements relating to the subject matter of this contract shall be considered, whether prior or subsequent to its signature, and shall be deemed null and void.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              2. Without prejudice to the ministry's right to take legal action against the party violating the contract, in case of a dispute arising between the two parties; the parties shall resort to the Ministry to settle the dispute amicably, and to take whatever action it deems fit.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              3. Where no provision is made in this contract, the provisions of Federal Law No. (9) of 2022, concerning domestic workers, its executive regulations, and other legal systems applicable in the Ministry of Human Resources and Emiratization, shall apply in this regard. The UAE courts shall be competent to hear any dispute relating to this contract.
            </td>
          </tr>

          

      </tbody></table>

      <table class="table" style="margin-top: 2px;">
          <tbody><tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p4/pkg4_2.png">
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              1. This contract shall end by the expiry of its term agreed upon by the two parties.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              2. The two parties may agree to terminate this contract before the expiry of its term, provided that the agreement on termination shall be in writing.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              3. The contract may be terminated by either party if the other party breaches any of its provisions.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              4. In the event that the worker abstains from work, or the Second Party wishes to terminate the contract, the First Party shall return the remaining amount for the agreed period of service.
            </td>
          </tr>

          

      </tbody></table>

      <table class="table" border="1">
          <tbody><tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop" colspan="2">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p4/8888.png">
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;" colspan="2">
              This contract has been made into three copies signed by both parties hereto, each party shall keep one copy, and the third copy shall be kept at the Ministry.
            </td>
          </tr>
          <tr>
            <th class="bordertop">First Party</th>
            <th class="bordertop">Second Party</th>
          </tr>

          <tr>
            <td align="left" class="bordertop" width="50%">Name : {{env('company_name') ?? "NA" }} </td>
            <td align="left" class="bordertop">Name : {{$con4?->customerInfo?->name}}</td>
          </tr>

          <tr>
            <td class="bordertop">Signature</td>
            <td class="bordertop">Signature  <img src="{{$con4?->signature}}" style="width: 50%; max-width: 200px;"></td>
          </tr>
         
      </tbody></table>
      Date: {{$con4?->created_at}} </div>
</div>





</body></html>