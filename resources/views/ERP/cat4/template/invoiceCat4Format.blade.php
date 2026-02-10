<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Home care</title>
    <link rel="stylesheet" href="{{ asset('backend/external/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/external/css/style.css') }}">
</head>
<body>

<div class="print-button-container">
        <button onclick="printInvoice()" class="btn btn-primary">Print Invoice</button>
    </div>

        <div class="main">
            <div class="row mx-0">
                <div class="col-7">
                   <div class="d-flex   p-4   align-items-center">
                       <div>
                       <img style="width: 90px; height: 90px; padding-right: 20px;" class="logo" src=" {{ env('logo') }}" alt="">
                    
                      </div>
                       <div>
                           <span class="fw-bolder">Tax Invoice</span> <br>
                            <span class="fw-bolder">{{env('company_name') ?? "NA" }}</span>
                            <br>
                           <span class="fw-bolder">TRN:{{env('company_trn') ?? "NA" }}</span>
                       </div>
                   </div>
                </div>
                <div class="col-5   p-3">

                        <div class="row w-100   ">
                            <div class="col-6  ">
                                <span class="fw-bolder">Invoice Number</span>
                                <span class="fw-bolder">رقم الفاتورة</span>
                            </div>
                            <div class="col-6   d-flex justify-content-center align-items-center">
                               <span class="normalFont"> {{$invDetails[0]?->refCode}}</span>
                            </div>
                        </div>

                    <div class="row w-100   ">
                        <div class="col-6  ">
                                    <span class="fw-bolder">accrued date</span>
                                    <span class="fw-bolder"> تاريخ الاستحقاق</span>
                        </div>
                        <div class="col-6   d-flex justify-content-center align-items-center">
                            <span class="normalFont">{{$invDetails[0]?->date}}</span>
                        </div>
                    </div>

                </div>
            </div>

            
            <div class="section">
                <div class="d-flex justify-content-center">
                   <div  style="width:450px" class="section1">

               
                   <div class="  w-100 p-1   d-flex justify-content-end   mx-0  ">
                           <div class="col-6      d-flex justify-content-center  ">
                         
                            <div>
                                <span  class="text-dark title">Customer</span>
                                <br>
                                <span class="text-dark">اسم الزبون</span>

                            </div>

                           </div>
                           <div class="d-flex col-6  align-items-center">
                           {{$invDetails[0]?->accountLedger?->ledger}} | {{$invDetails[0]['customerInfo']?->phone}}
                           </div>
                       </div>

                     
                       <div class="  w-100 p-1   d-flex justify-content-end   mx-0  ">
                           <div class="col-6      d-flex justify-content-center  ">
                         
                            <div>
                                <span  class="text-dark title"> Contract</span>
                                <br>
                                <span class="text-dark">الرقم المرجعي</span>

                            </div>

                           </div>
                           <div class="d-flex col-6  align-items-center">
                           {{$invDetails[0]?->contract_ref}}
                           </div>
                       </div>

                       <div class="  w-100 p-1   d-flex justify-content-end   mx-0  ">
                           <div class="col-6      d-flex justify-content-center  ">

                            <div>
                                <span class="text-dark  title">Maid</span>
                                <br>
                                <span class="text-dark">عاملة نظافة</span>
                            </div>

                           </div>
                    <div class="d-flex col-6     align-items-center">
                               <span class="normalFont subtitle text-dark">Name: {{$invDetails[0]?->maidInformation?->name}} <br>Nationality: {{$invDetails[0]?->maidInformation?->nationality}} </span>
                   </div>
                </div>
          


            </div>
        </div>
                <div class=" w-100 d-flex justify-content-center"  >
                  <div>
                      <table style="width:450px" class=" table text-center" >
                          <thead>
                          <tr  >
                              <td >SL.NO.</td>
                              <td class="rowTable"    >DESCRIPTION <br>الخدمة وصف</td>
                           
                          
                              <td class="rowTable" >TOTAL <br>  اجمالي السعر</td>
                          </tr>
                          </thead>
                          <tbody>
                          <tr class="bg-white text-dark">
                              <th scope="row td"  >1</th>
                              <td class="text-dark td  ">
                                   
                              Company Charge
                               
                              </td>
                             
                       
                        
                              <td class="text-dark td">{{ $invDetails[0]?->amount-$invDetails[1]?->amount }} AED
                          <tr class="bg-white text-dark">
                              <th scope="row td"  >2</th>
                              <td class="text-dark td  ">
                                   
                             Maid salary
                               
                              </td>
                             
                             
                        
                              <td class="text-dark td"> {{$invDetails[1]?->amount}} AED</td>

                          </tr>
                          <tr class="bg-white text-dark">
                              <th></th>
                              <td class="fw-bolder fs-5 total">
                                  Total
                              </td>
                             
                              <td class="text-dark td ">{{$invDetails[0]?->amount}}</td>
                           

                          </tr>

                          </tbody>

                      </table>
                  </div>
                </div>
                <div class=" w-100 d-flex justify-content-center"  >
                <div class="d-flex justify-content-between w-100  ">
                        <table class=" table w-100   mx-3 text-center" >
                            <thead>
                            <tr>
                                <td colspan="4" class="text-center" >Vat Analysis </td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="bg-white text-dark">
                                <td  class="text-dark td" >Taxable Value <br>{{ $invDetails[0]?->amount-$invDetails[1]?->amount }} AED</th>
                                <td class="text-dark td ">Vat% <br> 5%</td>
                                <td class="text-dark  td">Vat <br> Amount {{ Round($invDetails[0]?->amount-$invDetails[1]?->amount) -  Round(($invDetails[0]?->amount-$invDetails[1]?->amount)/1.05 )   }} AED</td>
                             </tr>


                            </tbody>

                        </table>
                        <table class=" secondTable table mx-3 text-center" >
                            <thead>
                            <tr>
                                <td colspan="4" class="text-center" >Total Payable </td>
                                <td colspan="4" class="text-center" > {{$invDetails[0]?->amount}} AED </td>
                            </tr>
                            </thead>
                            <thead>
                       
                            </thead>
                            <thead>
                            <tr>
                                <td colspan="4" class="text-center" >Closing  Balance </td>
                                <td colspan="4" class="text-center" >{{ $customerClosingBalance[0]['closing_balance']}}  AED </td>
                            </tr>

                            @foreach($rv as $r)
                               <td>
                               {{$r?->created_by}}  {{$r?->amount}} AED  {{$r?->date}} {{$r?->accountLedger->ledger}} 
                              </td>
 
                            @endforeach
                            </thead>


                        </table>



                </div></div>
                <div class="d-flex container justify-content-left mt-3">
                        <div class=" d-flex justify-content-center">
                           <div>
                               <div class=" fw-bold titleCreated">CREATED/UPDATED BY:</div>
                               <div class="subtitle subtitleCreated mt-2">User : {{$invDetails[0]?->created_by}}</div>
                               <div  class="subtitle subtitleCreated">Date: {{$invDetails[0]?->created_at}}</div>
                             
                           </div>
                        </div>
                      
                    </div>
                <div class="d-flex justify-content-end">
                    <div >
                        <div>Follow Us</div>
                        <div class="subtitle mt-2">Company</div>
                        <div class="mt-2">
                            <img class="contact-icon" src=" {{asset('backend/assets/images/fa-brands_facebook.png') }}" alt="">
                            <img   class="contact-icon" src="{{asset('backend/assets/formkit_twitter.png') }}" alt="">
                            <img  class="contact-icon" src="{{asset('backend/assets/images/fa_snapchat(1).png') }}" alt="">
                            <img   class="contact-icon" src="{{asset('backend/assets/images/fa_snapchat.png') }}" alt="">
                        </div>
                    </div >


                   
                </div>
                <!-- <footer class="mb-5 mt-4">
                    <div class="d-flex justify-content-around" style="font-size: 12px">

                   
                        <div class="footerFont">
                        <hr>
                            Tadbeer - Home Care <br>
                            PO Box 36067 Dubai , United Arab Emiraes <br>
                            AI Wasl Road, Oppisite Box Park, Villa # 462 <br>
                            Jumairah 2 - Dubai, UAE <br>
                            Telephone +971  4  343 9977 <br>
                            Fax +971  4  343  9988
                        </div>


                        
                        <div dir="rtl"  class="footerFont">
                        <hr>
                            تدبير - هوم كير <br>
                            ص.ب 36067 دبي الامارات العربية المتحدة <br>
                            شارع الوصل مقابل بوكس بارك فيلا رقم 462 <br>
                            جميرا 2 دبي الامارات العربية المتحدة<br>
                            تليفون +971  4  343 9977 <br>
                            فاكس +971  4  343  9988
                        </div>

                    </div>
                </footer> -
            </div>
        </div>


<script src="{{ asset('backend/external/js/bootstrap.min.js') }}"></script>

<script>
        function printInvoice() {
            window.print();
        }
    </script>
</body>
</html>