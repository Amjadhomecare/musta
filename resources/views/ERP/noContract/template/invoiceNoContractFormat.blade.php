<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>GG</title>
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
                           <span class="fw-bolder">Invoice</span> <br>
                            <span class="fw-bolder">{{env('company_name') ?? "NA" }}</span><br>
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
                               <span class="normalFont">{{$invDetails[0]?->refCode}}</span>
                            </div>
                        </div>

                    <div class="row w-100   ">
                        <div class="col-6  ">
                                    <span class="fw-bolder">Date & Time</span>
                                    <span class="fw-bolder">التاريخ و الوقت</span>
                        </div>
                        <div class="col-6   d-flex justify-content-center align-items-center">
                            <span class="normalFont">{{$invDetails[0]?->created_at}}</span>
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
                         
                           {{$invDr[0]?->accountLedger?->ledger }} ||   {{$invDr[0]['customerInfo']?->phone }}
                           </div>
                 
                       </div>


                              
                   <div class="  w-100 p-1   d-flex justify-content-end   mx-0  ">
                           <div class="col-6      d-flex justify-content-center  ">
                         
                            <div>
                                <span  class="text-dark title">Maid</span>
                                <br>
                                <span class="text-dark">اسم الخادمة</span>

                            </div>

                           </div>
                           <div class="d-flex col-6  align-items-center">
                         
                           {{$invDr[0]?->maidRelation?->name}}
                           </div>
                 
                       </div>

                       <div class="  w-100 p-1   d-flex justify-content-end   mx-0  ">
                           <div class="col-6      d-flex justify-content-center  ">
                         
                            <div>
                                <span  class="text-dark title">Invoice</span>
                                <br>
                                <span class="text-dark">رقم الفاتورة</span>

                            </div>

                           </div>
                           <div class="d-flex col-6  align-items-center">
                           {{$invDetails[0]?->refCode}}
                           </div>
                         </div>    
                        </div>
                    </div>

                    


                <div class=" w-100 d-flex justify-content-center"  >
                  <div>
                      <table style="width:450px" class=" table text-center" >
                          <thead>
                          <tr>
                              <td >SL.NO.</td>
                              <td class="rowTable">DESCRIPTION <br>الخدمة وصف</td>    
                              <td class="rowTable">TOTAL <br>  اجمالي السعر</td>
                          </tr>
                          </thead>
                          <tbody>
                            @php
                             $ref = 1
                            @endphp
                          @foreach ($invCr as $key )
                          
                          <tr class="bg-white text-dark">
                              <th scope="row td"  > {{$ref++}}</th>
                              <td class="text-dark td  ">
                                   
                              {{ $key?->accountLedger?->ledger}}
                               
                              </td>    
                              <td class="text-dark td">{{ $key?->amount}} AED
                              <tr class="bg-white text-dark">
                            
                         @endforeach

                          </tr>
                          <tr class="bg-white text-dark">
                              <th></th>
                              <td class="fw-bolder fs-5 total">
                                  Total
                              </td>
                             
                              <td class="text-dark td ">{{$invDr[0]?->amount}}</td>
                           

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
                                <td colspan="4" class="text-center" > Application refrence  </td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="bg-white text-dark">
                                <td  class="text-dark td" > DW</th>
                                  @foreach($invDr as $inv)             
                                <td  class="text-dark td" >{{ $inv?->notes }}</th>
                                 @endforeach
                             </tr>


                            </tbody>

                        </table>
                        <table class=" secondTable table mx-3 text-center" >
                            <thead>
                            <tr>
                                <td colspan="4" class="text-center" >Total Payable </td>
                                <td colspan="4" class="text-center" >{{ $invDr[0]?->amount }} AED </td>
                            </tr>
                            </thead>
                            <thead>
                       
                            </thead>
                            <thead>
                            <tr>
                                <td colspan="4" class="text-center" >Invoice Balance </td>
                                <td colspan="4" class="text-center" >{{$invDr[0]?->invoice_balance}}AED </td>
                            </tr>
                            </thead>


                        </table>



                </div></div>
                <div class="d-flex container justify-content-around mt-3">
                        <div class=" d-flex justify-content-center">
                           <div>
                               <div class=" fw-bold titleCreated">CREATED/UPDATED BY:</div>
                               <div class="subtitle subtitleCreated mt-2">User : {{$invDetails[0]?->created_by}}</div>
                               <div  class="subtitle subtitleCreated">Date: {{$invDetails[0]?->created_at}}</div>
                             
                           </div>
                        </div>
                        <div>
                            <h1 class="fs-5 fw-bold titleCreated">RECEIPT:</h1>
                            @foreach($allRV as $rv)
                                            <li>{{ $rv?->date }},{{ $rv?->created_by }}, {{ $rv?->accountLedger->ledger }}, {{ $rv?->amount }} AED</li>
                            @endforeach
                        </div>
                    </div>
                <div class="d-flex justify-content-end">
                    <div >
                  
                        <div class="mt-2">
                            <img class="contact-icon" src=" {{asset('backend/assets/images/fa-brands_facebook.png') }}" alt="">
                            <img   class="contact-icon" src="{{asset('backend/assets/formkit_twitter.png') }}" alt="">
                            <img  class="contact-icon" src="{{asset('backend/assets/images/fa_snapchat(1).png') }}" alt="">
                            <img   class="contact-icon" src="{{asset('backend/assets/images/fa_snapchat.png') }}" alt="">
                        </div>
                    </div >
                </div>
                <footer class="mb-5 mt-4">
                    <div class="d-flex justify-content-around" style="font-size: 12px">
                        <div class="footerFont">
                        <hr>
               
                        </div>

                        <!-- <div dir="rtl"  class="footerFont">
                        <hr>
                            تدبير - هوم كير <br>
                            ص.ب 36067 دبي الامارات العربية المتحدة <br>
                            شارع الوصل مقابل بوكس بارك فيلا رقم 462 <br>
                            جميرا 2 دبي الامارات العربية المتحدة<br>
                            تليفون +971  4  343 9977 <br>
                            فاكس +971  4  343  9988
                        </div> -->

                    </div>
                </footer>
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