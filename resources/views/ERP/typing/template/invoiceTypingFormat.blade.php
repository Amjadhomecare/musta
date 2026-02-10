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
            <div class="row mx-0 ">
                <div class="col-7">
                   <div class="d-flex   p-4   align-items-center">
                       <div>
                       <img style="width: 90px; height: 90px; padding-right: 20px;" class="logo" src=" {{ env('logo') }}" alt="">

                      </div>
                       <div>
                           <span class="fw-bolder">Tax Invoice</span> <br>
                            <span class="fw-bolder">{{env('company_name') ?? "NA" }}</span><br>
                           <span class="fw-bolder">TRN :{{env('company_trn') ?? "NA" }}  </span>
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
                               <span class="normalFont">{{$invDetails?->refCode}}</span>
                            </div>
                        </div>

                    <div class="row w-100   ">
                        <div class="col-6  ">
                                    <span class="fw-bolder">Date & Time</span>
                                    <span class="fw-bolder">التاريخ و الوقت</span>
                        </div>
                        <div class="col-6   d-flex justify-content-center align-items-center">
                            <span class="normalFont">{{$invDetails?->created_at}}</span>
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
                                @if($invDr->isNotEmpty())
                                    {{ $invDr?->first()?->accountLedger?->ledger }} || {{ $invDr?->first()?->customerInfo?->phone }}
                                @else
                                    No customer details available.
                                @endif
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
                                {{$invDetails?->refCode}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class=" w-100 d-flex justify-content-center"  >
                    <div>
                        <table style="width:450px" class="table text-center">
                            <thead>
                            <tr>
                                <th>SL.NO.</th>
                                <th>DESCRIPTION <br>الخدمة وصف</th>
                                <th>TOTAL <br>اجمالي السعر</th>
                                <th>service<br>طباعة الخدمة</th>
                                <th>VAT<br>الضريبة المضافة</th>
                            </tr>
                            </thead>
                            <tbody>
                                @php $ref = 1; @endphp
                                @if($totalPreConnection->isNotEmpty())
                                    @foreach($totalPreConnection as $chargeName => $chargeDetails)
                                        <tr class="bg-white text-black text-dark">
                                            <th scope="row">{{$loop?->iteration}}</th>
                                            <td class="text-black text-dark">{{$chargeName}}</td>
                                            <td class="text-black text-dark">{{$chargeDetails['total']}} AED</td>
                                            <td class="text-black text-dark">{{$chargeDetails['vatAmount'] /0.05}} AED</td>
                                            <td class="text-black text-dark">{{$chargeDetails['vatAmount']}} AED</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center">No charges available.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class=" w-100 d-flex justify-content-center"  >
                    <div class="d-flex justify-content-between w-100  ">
                        <table class=" table w-100   mx-3 text-center" >
                            <thead>
                                <tr>
                                    <td colspan="4" class="text-center" > Application refrence </td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-white text-dark">
                                    <td  class="text-dark td" > DW</th>
                                    @if($invDr->isNotEmpty())
                                        @foreach($invDr as $inv)
                                            <td class="text-dark td">{{ $inv?->notes }}</td>
                                            <td class="text-dark td">{{ $inv?->pre_connection_name }}</td>
                                        @endforeach
                                    @else
                                        <td class="text-dark td" colspan="3">No debit transaction details available.</td>
                                    @endif
                                </tr>
                            </tbody>
                        </table>
                        <table class=" secondTable table mx-3 text-center" >
                            <thead>
                                <tr>
                                    <td colspan="4" class="text-center" >Total Payable </td>
                                    @if($invDr->isNotEmpty())
                                        <td id="ministry" colspan="4" class="text-center">{{ $invDr?->first()?->amount }} AED</td>
                                    @else
                                        <td colspan="4" class="text-center">No payable amount available.</td>
                                    @endif
                                </tr>
                            </thead>
                            <thead>
                            </thead>
                            <thead id="hahah" >
                                <tr>
                                    <td colspan="6" class="text-center" >Closing Balance</td>
                                    <td colspan="6" class="text-center">
                                    @if($invDr->isNotEmpty())
                                        <td colspan="4" class="text-center">
                                            {{ $invDr?->first()?->invoice_balance}} AED
                                        </td>
                                    @else
                                        <td colspan="4" class="text-center">No balance available.</td>
                                    @endif
                                </tr>
                                <tr>
                                    <td colspan="20">
                                        <ul style="list-style-type: none; padding-left: 0; font-size: 12px;">
                                            @foreach($allRV as $rv)
                                            <li>{{ $rv?->date }},{{ $rv?->created_by }}, {{ $rv?->accountLedger->ledger }}, {{ $rv?->amount }} AED</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                <div class="d-flex container justify-left-around mt-3">
                        <div class=" d-flex justify-content-center">
                           <div>
                               <div class=" fw-bold titleCreated">CREATED/UPDATED BY:</div>
                               <div class="subtitle subtitleCreated mt-2">User : {{$invDetails?->created_by}}</div>
                               <div  class="subtitle subtitleCreated">Date: {{$invDetails?->created_at}}</div>

                           </div>
                        </div>
                </div>
                <div class="d-flex justify-content-end">
                    <div >
                        <div>Follow Us</div>
                        <div class="subtitle mt-2"></div>
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
                            </footer> -->
            </div>
        </div>


        <script src="{{ asset('backend/external/js/bootstrap.min.js') }}"></script>

        <script>
                function printInvoice() {
                    window.print();
                }
        </script>


<script>
    let hasTriggered = false; 

    document.addEventListener("keydown", function(event) {
        if (!hasTriggered && (event.key === "o" || event.key === "O")) { 
            updateInvoiceValues();
            hasTriggered = true; 
        }
    });

    function updateInvoiceValues() {
        let totalSum = 0; 

        document.querySelectorAll("tbody tr").forEach((row) => {
            let totalCell = row.cells[2]; 
            let serviceCell = row.cells[3]; 
            let vatCell = row.cells[4]; 

            if (totalCell && serviceCell && vatCell) {
                let total = parseFloat(totalCell.textContent) || 0;
                let service = parseFloat(serviceCell.textContent) || 0;
                let vat = parseFloat(vatCell.textContent) || 0;
                let newTotal = total - service - vat;

                totalCell.textContent = newTotal.toFixed(2) + " AED";
                serviceCell.textContent = "0 AED";
                vatCell.textContent = "0 AED";

                totalSum += newTotal;
            }
        });

        let totalPayableCell = document.getElementById("ministry");
        if (totalPayableCell) {
            totalPayableCell.textContent = totalSum.toFixed(2) + " AED";
        }

        let closingBalanceSection = document.getElementById("hahah");
        if (closingBalanceSection) {
            closingBalanceSection.hidden = true; 
        }
    }
</script>


    </body>
</html>
