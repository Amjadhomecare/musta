<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Example 1</title>
 
 <style>
  .clearfix:after {
    content: "";
    display: table;
    clear: both;
  }

  a {
    color: #5D6975;
    text-decoration: underline;
  }

  body {
    position: relative;
    width: 16cm;  
    height: 29.7cm; 
    margin: 0 auto; 
    color: #001028;
    background: #FFFFFF; 
    font-family: Arial, sans-serif; 
    font-size: 12px; 
    font-family: Arial;
  }

  header {
    padding: 10px 0;
    margin-bottom: 30px;
  }

  #logo {
    text-align: center;
    margin-bottom: 10px;
  }

  #logo img {
    width: 90px;
  }

  h1 {
    border-top: 1px solid  #5D6975;
    border-bottom: 1px solid  #5D6975;
    color: #5D6975;
    font-size: 2.4em;
    line-height: 1.4em;
    font-weight: normal;
    text-align: center;
    margin: 0 0 20px 0;
  }

  #project {
    float: left;
  }

  #project span {
    color: black;
    text-align: right;
    width: 52px;
    margin-right: 10px;
    display: inline-block;
    font-size: 0.8em;
  }

  #company {
    float: right;
    text-align: right;
  }

  #project div,
  #company div {
    white-space: nowrap;        
  }

  table {
    width: 100%; /* Adjust width to fill the container */
    margin: 0 auto; /* Center the table */
    border-collapse: collapse;
    border-spacing: 0;
    margin-bottom: 20px;
    border: 2px solid #5D6975; /* Thick border */
  }

  table th,
  table td {
    text-align: center;
    border: 2px solid #5D6975; /* Thick border */
  }

  table th {
    padding: 10px 20px; /* Increase padding */
    color: black;
    background-color: #F5F5F5; /* Alternate row background */
    font-weight: normal;
  }

  table td {
    padding: 15px 20px; /* Increase padding */
  }

  table td.total {
    font-size: 1.2em;
    font-weight: bold; /* Make total cell bold */
  }

  table td.grand {
    border-top: 2px solid #5D6975; /* Thick border */
  }

  .voucher-signature {
    text-align: right;
    margin-top: 40px;
    font-style: italic;
  }

  .signature-space {
    border-bottom: 1px solid #000;
    width: 200px;
    display: inline-block;
  }

  /* Red bold style for net amount */
  .net-amount {
    font-weight: bold;
    color: red;
  }
</style>

</head>
<body>


  <header class="clearfix">


    <h1 >CREDIT MEMO {{$data->memo_ref}}</h1>
   
    <div id="project">
      <h1>{{env('company_name')?? "company name" }}</h1>
      <table>
      <thead  >
          <tr >             
              <th style="background: #67C6E3 !important;" ><span>CLIENT</span></th>
              <th style="background: #67C6E3 !important;" ><span>MAID</span></th>
              <th style="background: #67C6E3 !important;" ><span>REASON</span></th>

             

          </tr>
      </thead>
        <tr>
          <td> {{$data->customer}}</td>
          <td> {{$data->maid}}</td>
          <td> {{$data->note}}</td>
       </tr>
      </table>
      <!-- Table for dates -->
      <table>

      <thead>
          <tr> 
                
              <th style="background: #378CE7 !important;"><span>STARTED DATE</span></th>
              <th style="background: #378CE7 !important;"><span>RETURNED DATE</span></th>
       

          </tr>
      </thead>
        <tr>
          <td> {{$data->started_date}}</td>
  
          <td> {{$data->returned_date}}</td>
        </tr>
      </table>
    </div>
  </header>
  <main>
    <table>
      <thead>
        <tr>
          <th style="background: #5B8FB9 !important;" class="desc">DESCRIPTION</th>      
          <th style="background: #5B8FB9 !important;">TOTAL</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="service">Amount Received From Customer / Contract ref:{{$data->contract_ref}} </td>
          <td class="total">{{$data->amount_received}}</td>
        </tr>
        <tr>
          <td class="service">Deduction</td>
          <td class="total">{{$data->amount_deduction}}</td>
        </tr>
        <tr>
          <td class="service">Salary for maid</td>
          <td class="total">{{$data->amount_for_maid}}</td>
        </tr>
      </tbody>
    </table>
    <!-- Net amount -->
    <div class="net-amount">
      <h2>Total Amount To Refunded: {{$data->refunded_amount}}</h2>
    </div>
    <div class="voucher-signature">
      <p>Created By Signature:</p>
      <div class="signature-space"></div>
      <p>{{$data->created_by}}</p>

      <p>Customer Signature:</p>
      <div class="signature-space"></div>
      <p>{{$data->customer}}</p>

   <br>
      <div class="signature-space"></div>

      <p>Managment Signature:</p>
  </div>
  </main>
  <footer>
  </footer>
</body>
</html>
