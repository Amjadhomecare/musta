<html lang="en"><head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Contract</title>
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://homecaredxb.me/assets/css/style-print.css" type="text/css" media="print">
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
border:1px solid;
  font-family:calibri;
  
}
.bordertop {
border-top: 1px solid !important;
}
 table .tr_head {color: #FFF;font-weight: bold;font-size:18px;border:1px solid #000 }
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
  <div class="row" style="width: 210mm;border: 2px solid;text-align: center;margin-left: auto;margin-right: auto;">
      <a href="/get/full/categoryone-contract-arabic/{{$conDetails?->contract_ref}}" class="btn btn-xs btn-danger no-print pull-right" style="width: 100%;margin-top: 10px;margin-bottom: 10px;">
        <i class="fa fa-print"></i> Arabic View </a>

        <div> 

     <button type="button" class="btn btn-xs btn-default no-print pull-right" style="width: 100%;background: #dbdbdb;" onclick="printContent('print');">
      <i class="fa fa-print"></i> Print    </button>
  </div>
</div>
<div class="container" style="width: 210mm" id="print">  

  <div class="row" style="border: 1px solid">
      <table class="table" style="margin-top: 2px;border-top:2px solid #000;border-bottom:2px solid #000;margin-bottom:0;">
           <tbody><tr>
            <td align="center" rowspan="2" style="">
              <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/mohregg.jpg" alt="logo" width="100%">
            </td>
            
          </tr>
          
      </tbody></table>
     <table class="table" style="margin-bottom:0;">
          <tbody><tr class="tr_head" style="padding:0">
            <td style="padding:0;background-color:#bf8f00;border-bottom:2px solid #000;border-top:2px solid #000;color: #000; font-family: Calibri, sans-serif;text-align: center" align="center" class="bordertop">
                  <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/Employmment+Contract+Traditional+Package+for+hiring.png">
                
            </td>
          </tr>
      </tbody></table>

      <table class="table" border="1" style="margin-bottom:0;">

          <tbody><tr>
            <td align="center" class="bordertop" style="font-weight:bold">
              The two parties have agreed to hire the domestic worker mentioned in the statements and the articles herein, and that the second
              party will recruit the domestic worker for a two-year period from the date of the first party’s entry into the country or from the date
              of receiving the worker from the office.

            </td>
          </tr>

          
          
      </tbody></table>
    <table class="table" border="1" style="margin-bottom:0;">

          <tbody><tr>
            <td align="center" class="bordertop" style="border-top:2px solid #000;border-bottom:1px solid #000;font-weight:bold">
              
Contact Number: <input readonly="" style="width:115px;text-align:center;background-color:rgb(236,241,246);border:1px solid gray;padding:5px" type="text" name="contact" placeholder="{{$conDetails?->contract_ref}}">


            Contract Status: <input readonly="" style="width:65px;text-align:center;background-color:rgb(236,241,246);border:1px solid gray;padding-top:5px;padding-bottom:5px" type="text" name="status" placeholder="Active">

            Date: <input readonly="" style="width:100px;text-align:center;background-color:rgb(236,241,246);border:1px solid gray;padding-top:5px;padding-bottom:5px" type="text" name="date" placeholder="{{$conDetails?->started_date}}">

            Emirates: <input readonly="" style="width:150px;text-align:center;background-color:rgb(236,241,246);border:1px solid gray;padding-top:5px;padding-bottom:5px" type="text" name="emirates" placeholder="{{env('company_emirate') ?? 'Dubai' }}">
            </td>
          </tr>

          
          
      </tbody></table>

    
    <table class="table" style="margin:0;padding:0;font-weight:bold" border="1">
         
           <tbody><tr style="padding:0">
              <td align="center" rowspan="4" class="tr_head" style="padding:0;background:#bf8f00;color:#000;width: 6%;writing-mode: vertical-lr">
                 <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/first+party.png">
                 
              </td>
              <td colspan="2" class="bordertop" style="width:94%;">{{env('company_name') ?? "NA" }}  </td>
          </tr>
           <tr>
              <td colspan="2" class="bordertop">Represented by :{{$conDetails?->created_by}} </td>
               
            </tr>
      
          <tr>
            <td class="bordertop" style="width:47%;">E-mail : {{env('company_email') ?? "NA" }} </td>
             <td class="bordertop" style="width:47%;">P.O </td>
        </tr>

        <tr>
          <td class="bordertop" style="width:47%;">Phone Number: {{env('company_phone') ?? "NA" }} 
          <td class="bordertop" style="width:47%;">License No.: {{env('company_License_no') ?? "NA" }} </td>
        </tr>
     
      </tbody></table>
    
    <table class="table" style="margin:0;padding:0;font-weight:bold" border="1">
           <tbody><tr>
              <td align="center" class="tr_head" rowspan="4" style="padding:0;background:#bf8f00;color:#000;width: 6%;writing-mode: vertical-lr">
                 <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/Second+Party.png">
              </td>
              <td colspan="2" class="bordertop" style="width:94%;">Sponsor Name:  {{$conDetails?->customerInfo?->name}}</td>
          </tr>
        

        <tr>
          <td class="bordertop" style="width:94%;f">Nationality: {{$conDetails?->customerInfo?->nationality}}</td>
        </tr>

        <tr>
          <td class="bordertop" style="width:94%;">EMIRATES ID No.:{{$conDetails?->customerInfo?->idNumber}}</td>
        </tr>

        <tr>
          <td class="bordertop" style="width:94%;f">Contact Number: {{$conDetails?->customerInfo?->phone}}</td>
        </tr>

       
      </tbody></table>
    <table class="table" style="margin:0;padding:0;font-weight:bold" border="1">
         
           <tbody><tr>
              <td align="center" rowspan="5" class="tr_head" style="padding:0;background:#bf8f00;color:#000;width: 6%;writing-mode: vertical-lr">
                  <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/Domestic+workers.png">
              </td>
              <td class="bordertop" colspan="2" style="width:94%;">Worker Name : {{$conDetails?->maidInfo?->name}}</td>
          </tr>
          
         <tr>
           
              <td colspan="2" class="bordertop" style="width:94%;">Profession : Servant</td>
          </tr>
      
          <tr>
                         <!-- <td class="bordertop" style="width:47%;">Birth Date : GG</td> -->
             <td class="bordertop" style="width:47%;">Nationality : {{$conDetails?->maidInfo?->nationality}}</td>
        </tr>
       
        <tr>
          <td class="bordertop" style="width:47%;">Gender : Female</td>
          <td class="bordertop" style="width:47%;">Age : {{$conDetails?->maidInfo?->age}}</td>
        </tr>
        <tr>

              <td colspan="2" class="bordertop" style="width:94%;">Salary : {{$conDetails?->maidInfo?->salary}}</td>
          </tr>
     
      </tbody></table>
    
      <table class="table" style="margin:0;font-weight:bold">
          <tbody><tr>
            <td style="padding:0;background: #bf8f00;color: #000;font-weight: bold;font-size:18px;;text-align:center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/Artical+one.png">
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">It was agreed upon that the second party shall pay the first party the recruitment fee amount of AED ( {{$conDetails?->amount}} )</td>
          </tr>
      </tbody></table>


      

      <table class="table" style="margin:0;font-weight:bold">
         <tbody><tr>
            <td style="padding:0;background: #bf8f00;color: #000;font-weight: bold;font-size:18px;;text-align:center" class="bordertop">
             <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/Articale+two.png">
            </td>
          </tr>
          <tr>
            <td align="left" style="border-top: none;">The domestic worker shall be under probation for a six-months period from the date of starting the work.</td>
          </tr>
      </tbody></table>

      <table class="table" style="margin:0;font-weight:bold">
          <tbody><tr>
            <td style="padding:0;background: #bf8f00;color: #000;font-weight: bold;font-size:18px;;text-align:center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/Artical+three.png">
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;font-weight: bold;">The first party is committed to the following :</td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              Providing a worker within a period not exceeding sixty days from the date of receiving the entry permit in case the worker is outside
the country.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              Issuing a license for the domestic worker in professions that require a license to practice them, unless the two parties agree otherwise.
Any other obligations stipulated by the relevant legal systems in force in the country.
            </td>
          </tr>

      </tbody></table>



      <table class="table" style="margin:0;font-weight:bold">
          <tbody><tr>
            <td style="padding:0; background: #bf8f00;color: #000;font-weight: bold;font-size:18px;;text-align:center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/Artical+four.png">
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;font-weight: bold;">The second party shall adhere to the following:</td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              1. Shall be held responsible to incur the administrative and government fees mandatory to obtain and complete the worker's and the
replacement worker’s permit.

            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              2. Shall employ the worker as per the work and the wage agreed upon before the recruitment.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              3.  Shall provide the worker's needs including housing, meals, appropriate clothes, and work requirements.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              4. Shall not and has no right to employ the domestic worker for third parties.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              5. Shall not collect any money from the worker.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              6. Shall pay the worker's wages on the due date and shall pay all of the worker’s dues within 10 days prior to the contract expiry
date.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              7.  Shall inform the Ministry within five days in the event of the worker’ absence from work without an approved reason.
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              8.  Shall commit to any other obligations stipulated in the relevant legal regulations in force in UAE.
            </td>
          </tr>


          
          <tr>
            <td align="left" style="border-top: none;">

           9.  The sponsor must complete the worker’s residency procedures within no more than 55 days from the date of receiving her. Failure to comply will result in the forfeiture of the guarantee provided to them.

             </td>
          </tr>
      </tbody></table>


      <table class="table" style="margin:0;font-weight:bold">
          <tbody><tr>
            <td style="padding:0; background: #bf8f00;color: #000;font-weight: bold;font-size:18px;;text-align:center" class="bordertop">
             <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/Artical+five.png">
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
              In event the first party breaches any of the agreed upon articles of this contract, the second party shall be entitled the right to
demand the following: 
            </td>
          </tr>

      </tbody></table>
    <table class="table" style="margin:0;font-weight:bold">
          <tbody><tr>
            <td style=" padding:0;background: #bf8f00;color: #000;font-weight: bold;font-size:18px;;text-align:center" class="bordertop">
             <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/firstPrior.png">
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
             In event the first party delays placing the worker at the employer's disposal for more than sixty days from the entry
permit receipt date, the employer shall be entitled the right to cancel the contract and shall be refunded recruitment and government
fees.  
            </td>
          </tr>

      </tbody></table>

    <table class="table" style="margin:0;font-weight:bold">
          <tbody><tr>
            <td style="padding:0; background: #bf8f00;color: #000;font-weight: bold;font-size:18px;;text-align:center" class="bordertop">
            <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/second+During.png">
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
             1. The employer shall be entitled the right to be refunded the remaining of sums paid to the recruitment office or be provided
substitute worker as per the employer’s choice. Government fees shall not be included in the refund value . 
            </td>
          </tr>
      <tr>
            <td align="left" style="border-top: none;">
             2. The working period is deducted from the recruitment amount if the worker completes first month.
            </td>
          </tr>
      <tr>
            <td align="left" style="border-top: none;">
             3. The recruitment office shall be held responsible to incur the
expenses of returning the worker to his/her country, in the event any of the following is proven or occurred.  
            </td>
          </tr>
      <tr>
            <td align="left" style="border-top: none;">
             A. In event the worker lacks professional competence and/or good personal behavior 
            </td>
          </tr>
      <tr>
            <td align="left" style="border-top: none;">
             B. In event it is proven that the worker's health is inapt. 
            </td>
          </tr>
      <tr>
            <td align="left" style="border-top: none;">
           C. In event the worker leaves work under circumstances other than the authorized ones.
            </td>
          </tr>
      <tr>
            <td align="left" style="border-top: none;">
             D. In event the contract is terminated by the worker's will  
            </td>
          </tr>
     
      </tbody></table>
      <table class="table" style="margin:0;font-weight:bold">
          <tbody><tr>
            <td style=" padding:0;background: #bf8f00;color: #000;font-weight: bold;font-size:18px;;text-align:center" class="bordertop">
            <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/THird+after+the+probation.png">
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
             1. Requesting the recruitment office to refund part of the recruitment amount in case the recruitment office has not been proven to
have a hand in any of the following two cases:
            </td>
          </tr>
      <tr>
            <td align="left" style="border-top: none;">
             A. In the event the worker cancels the contract after the probation period, without a reason related to the employer.
            </td>
          </tr>
      <tr>
            <td align="left" style="border-top: none;">
              B. In the event, after the probation period, the worker
leaves and stops working for the employer without an approved reason
            </td>
          </tr>
      <tr>
            <td align="left" style="border-top: none;">
            2. Requesting the recruitment office to refund the full amount of
the recruitment amount in case it is proven that the recruitment office has a hand in cases (A and B) Item No. 1, Paragraph
“Third”

            </td>
          </tr>
      
     
      </tbody></table>
     <table class="table" style="margin:0;font-weight:bold">
          <tbody><tr>
            <td style=" padding:0;background: #bf8f00;color: #000;font-weight: bold;font-size:18px;;text-align:center" class="bordertop">
            <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/the+refund.png">
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
             1. The refund amount shall be calculated for the second party when returning the domestic worker (during or after the
probation period) to the first party, as follows: (Total recruitment cost ÷ the worker's contract period in months) x the remaining
period of the worker's work contract term in months.
            </td>
          </tr>
      <tr>
            <td align="left" style="border-top: none;">
            2. The sums deducted from the recruitment value shall be deemed the operational fees payable to the first party, after the domestic
worker completes one month of work with the second party.
            </td>
          </tr>
     
     
      </tbody></table>

    
     <table class="table" style="margin:0;font-weight:bold">
          <tbody><tr>
            <td style=" padding:0; background: #bf8f00;color: #000;font-weight: bold;font-size:18px;;text-align:center" class="bordertop">
            <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/Artical+six.png">
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
             In the event of any dispute between the two parties of this contract (the employer and the recruitment office), the provisions of
Federal Law No. (9), 2022 relating to the domestic workers, its executive
            </td>
          </tr>
      <tr>
            <td align="left" style="border-top: none;">
            regulations, and other legal systems in force at the Ministry of Human Resources and Emiratisation in this regard shall apply. United
Arab Emirates courts shall be the competent authority to decide on any
dispute arising from this contract.
            </td>
          </tr>
     
     
      </tbody></table>
    <table class="table" style="margin:0;font-weight:bold">
          <tbody><tr>
            <td style=" padding:0;background: #bf8f00;color: #000;font-weight: bold;font-size:18px;;text-align:center" class="bordertop">
            <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/Artical+seven.png">
            </td>
          </tr>

          <tr>
            <td align="left" style="border-top: none;">
             This contract has been concluded in three copies after signature by the two parties, one copy has been received by the first party, the
            other by the second party, and the third copy has been deposited in the Ministry
            </td>
          </tr>
      
     
      </tbody></table>

     

      <table class="table" border="1" style="margin:0;font-weight:bold">
    <tbody>
        <tr>
            <th class="bordertop" style="padding:0;background:#bf8f00;color:#000;text-align:center">
                <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/first+party+sigb.png">
            </th>
            <th class="bordertop" style="padding:0;background:#bf8f00;color:#000;text-align:center">
                <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/second+party+sign.png">
            </th>
        </tr>
        <tr>
            <td align="left" class="bordertop" width="50%">Name : {{env('company_name') ?? "NA" }} </td>
            <td align="left" class="bordertop">Name : {{$conDetails?->customerInfo?->name }}</td>
        </tr>
        <!-- New row for the signature image -->
 
            <td class="bordertop">Signature </td>
            <td class="bordertop">Signature    <img src="{{$conDetails?->signature}}" style="width: 50%; max-width: 200px;"></td>
        </tr>
    </tbody>
</table>

   
    {{$conDetails?->created_at}} By User  {{$conDetails?->created_by}}
    </div>





</body>

</html>