<html dir="rtl" lang="ar"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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


table {
  
  font-family:calibri;
}
 body{
   font-family:calibri;
 }
.bordertop {
border-top: 1px solid !important;
}
 table .tr_head { background:#000;;color: #FFF;font-weight: bold;font-size:18px; }
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
      <a href="/get/full/categoryone-contract/{{$conDetails?->contract_ref}}" class="btn btn-xs btn-danger no-print pull-right" style="width: 100%;margin-top: 10px;margin-bottom: 10px;">
        <i class="fa fa-print"></i> English View      </a>

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
          
         <tbody><tr class="tr_head">
            <td dir="rtl" style="padding:0;background:#bf8f00;border-bottom:2px solid #000;border-top:2px solid #000;color: #000; font-family: Calibri, sans-serif;text-align: center" align="center" class="bordertop">
                 <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p1/img_ar_1.png" alt="logo" width="100%">
            </td>
          </tr>
      </tbody></table>
    
      <table class="table" border="1" style="margin-bottom:0;">

          <tbody><tr>
            <td dir="rtl" align="center" class="bordertop" style="font-weight:bold">
           
              اتفق الطرفان على استقدام العامل المساعد المذكور في
              البيانات وضمن البنود أدناه، على أن يعمل لدى الطرف الثاني
              لمدة سنتان من تاريخ دخوله الدولة أو من
              تاريخ تسلم العامل من مكتب الإستقدام
            </td>
          </tr>

          
          
      </tbody></table>
    <table class="table" border="1" style="margin-bottom:0;">

          <tbody><tr>
            <td align="center" class="bordertop" style="border-top:2px solid #000;border-bottom:1px solid #000;font-weight:bold">
              
 رقم العقد: <input readonly="" style="text-align:center;background-color:rgb(236,241,246);border:1px solid gray;padding:5px" type="text" name="contact" placeholder="{{$conDetails?->contract_ref}}">

                حالة العقد: <input readonly="" style="width:90px;text-align:center;background-color:rgb(236,241,246);border:1px solid gray;padding-top:5px;padding-bottom:5px" type="text" name="status" placeholder="Active">
              
               التاريخ: <input readonly="" style="width:90px;text-align:center;background-color:rgb(236,241,246);border:1px solid gray;padding-top:5px;padding-bottom:5px" type="text" name="status" placeholder="{{$conDetails?->started_date}}">
              
                 الإمارة: <input readonly="" style="width:150px;text-align:center;background-color:rgb(236,241,246);border:1px solid gray;padding-top:5px;padding-bottom:5px" type="text" name="emirates" placeholder="{{env('company_emirate') ?? 'Dubai' }}">

            
            </td>
            
          </tr>

          
          
      </tbody></table>



      
    <table class="table" style="margin:0;padding:0;font-weight:bold" border="1">
         
           <tbody><tr>
              <td align="center" rowspan="4" class="tr_head" style="padding:0;background:#bf8f00;color:#000;width: 6%;writing-mode: vertical-lr">
                 <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p1/img_ar_2.png" alt="logo" width="100%">
              </td>
              <td colspan="2" class="bordertop" style="width:94%;"> اسم المكتب الإستقدام : {{env('company_name') ?? "NA" }} </td>
          </tr>
          
      <tr>
          <td colspan="2" class="bordertop">و ينوب عنها :{{$conDetails?->created_by}}</td>
        </tr>
      
          <tr>
          <td class="bordertop" style="width:47%;">البريد الإلكتروني : {{env('company_email') ?? "NA" }} </td>
             <td class="bordertop" style="width:47%;">صندوق البريد : <span dir="ltr">{{env('company_po_box') ?? "NA" }}  </span></td>

        </tr>

        <tr>
          <td class="bordertop" style="width:47%;">رقم الهاتف: <span dir="ltr"> {{env('company_phone') ?? "NA" }} </span></td>
          <td class="bordertop" style="width:47%;">رقم الرخصة: <span dir="ltr"> {{env('company_License_no') ?? "NA" }} </span></td>
        </tr>
     
      </tbody></table>
    
    <table class="table" style="margin:0;padding:0;font-weight:bold" border="1">
           <tbody><tr>
              <td align="center" class="tr_head" rowspan="4" style="padding:0;background:#bf8f00;color:#000;width: 6%;writing-mode: vertical-lr">
                  <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p1/img_ar_3.png" alt="logo" width="100%">
              </td>
              <td colspan="2" class="bordertop" style="width:94%;">الاسم:{{$conDetails?->customerInfo?->name}}</td>
          </tr>
        

        <tr>
          <td class="bordertop" style="width:94%;f">الجنسية: {{$conDetails?->customerInfo?->nationality}} </td>
        </tr>

        <tr>
          <td class="bordertop" style="width:94%;"> رقم الهوية: <span dir="ltr">{{$conDetails?->customerInfo?->idNumber}}</span></td>
        </tr>

        <tr>
          <td class="bordertop" style="width:94%;f">رقم الهاتف: <span dir="ltr">{{$conDetails?->customerInfo?->phone}}</span></td>
        </tr>

       
      </tbody></table>
    <table class="table" style="margin:0;padding:0;font-weight:bold" border="1">
         
           <tbody><tr>
              <td align="center" rowspan="5" class="tr_head" style="padding:0;background:#bf8f00;color:#000;width: 6%;writing-mode: vertical-lr">
                 <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p1/img_ar_4.png" alt="logo" width="100%">
              </td>
              <td class="bordertop" colspan="2" style="width:94%;">الاسم : {{$conDetails?->maidInfo?->name}}</td>
          </tr>
          
         <tr>
           
              <td colspan="2" class="bordertop" style="width:94%;">المهنة : Servant</td>
          </tr>
      
          <tr>
                         <td class="bordertop" style="width:47%;">تاريخ الميلاد : {{$conDetails?->maidInfo?->dob}}</td>
             <td class="bordertop" style="width:47%;">الجنسية :{{$conDetails?->maidInfo?->nationality}}</td>
        </tr>

        <tr>
          <td class="bordertop" style="width:47%;">الجنس : Female</td>
          <td class="bordertop" style="width:47%;">العمر :  {{$conDetails?->maidInfo?->age}}</td>
        </tr>
        <tr>
           
              <td colspan="2" class="bordertop" style="width:94%;">الراتب :  {{$conDetails?->maidInfo?->salary}}</td>
          </tr>
     
      </tbody></table>
   
          

      

      <table class="table" style="font-weight:bold;font-size:16px;font-family:calibri">
          <tbody><tr style="background: #bf8f00;color: #000;">
            <td dir="rtl" align="center" class="bordertop" style="padding:0;">
               <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p1/img_ar_5.png" alt="logo" width="100%">
            </td>
          </tr>

          <tr>
            <td dir="rtl" align="right" style="border-top: none;">
            تم الاتفاق على أن يدفع الطرف الثاني
              إلى الطرف الأول رسوم الإستقدام وقدرها
               (  {{$conDetails?->amount}} )درهم بالطريقة التالية تم االتفاق
               على أن يدفع الطرف الثاني
               إلى الطرف الأول رسوم الإستقدام وقدرها
               () : درهم بالطريقة التالية الدفعة الثانية ()درهم عند
استلام صاحب .العمل للعامل المساعد
            </td>
          </tr>
      </tbody></table>
    
      <table class="table" style="margin:0;font-weight:bold;font-size:16px;font-family:calibri">
          <tbody><tr style="background: #bf8f00;color: #000;">
            <td dir="rtl" align="center" style="padding:0;" class="bordertop">
                <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p1/img_ar_6.png" alt="logo" width="100%">
            </td>
          </tr>

          <tr>
            <td dir="rtl" align="right" style="border-top: none;">
              يوضع العامل تحت التجربة لمدة ستة أشهر من تاريخ تسلمه العمل
            </td>
          </tr>
      </tbody></table>

      <table class="table" style="margin-bottom:0;font-weight:bold;font-size:16px;font-family:calibri">
          <tbody><tr style="background: #bf8f00;color: #000;">
            <td align="center" class="bordertop" style="padding:0;">
            <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p1/img_ar_7.png" alt="logo" width="100%">
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;font-weight: bold;">يلتزم الطرف الأول بالآتي </td>
          </tr>


          <tr>
            <td dir="rtl" align="right" style="border-top: none;">
                  <ol start="1" dir="rtl" style="line-height: 30px;font-size:16px;color:#000"> 
                        <li> توفير عامل  خلال مدة لا تتجاوز ستين يوم من تاريخ استلام إذن الدخول في حالة إذا كان العامل خارج الدولة.
                    </li>
                    <li>اصدار ترخيص للعامل المساعد في المهن التي تستلزم ترخيصا لممارستها ، إلا إذا اتفق الطرفان على خلاف ذلك.</li>

                    <li> اية التزامات اخرى تنص عليها الانظمة القانونية ذات الشأن المعمول بها في الدولة. </li>
                        
                  </ol>
            </td>
          </tr>
      </tbody></table>



      <table class="table" style="margin-bottom:0;font-weight:bold;font-size:16px;font-family:calibri">
           <tbody><tr style="background: #bf8f00;color: #000;">
            <td align="center" class="bordertop" style="padding:0;">
            <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p1/img_ar_8.png" alt="logo" width="100%">
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;font-weight: bold;">ﯾﻠﺗزم اﻟطرف اﻟﺛﺎﻧﻲ ﺑﺎﻵﺗﻲ </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
                  <ol start="1" dir="rtl" style="line-height: 30px;font-size:16px;color:#000">
                       <li>تحمل الرسوم الإدارية و الحكومية اللازمة لاستخراج واستكمال تصريح العامل والعامل البديل.
                        </li>
                        <li>تشغيل العامل وفقا للعمل والأجر المتفق عليه قبل استقدامه.</li>
                        <li>توفير احتياجات العامل من مسكن ووجبات الطعام والملابس الملائمة ومستلزمات العمل.
                        </li>
                        <li> لا يحق لصاحب العمل تشغيل العامل لدى الغير.
                        </li>
                        <li> عدم تحصيل أية مبالغ مالية من العامل.</li>
                        <li>سداد أجر العامل في موعد الاستحقاق وأداء جميع مستحقاته خلال 10 أيام من تاريخ انتهاء العقد.
                        </li>
                        <li>بلاغ الوزارة خلال خمسة أيام من تغيب العامل عن العمل دون سبب مشروع.</li>
                        <li>اية التزامات اخرى تنص عليها الانظمة القانونية ذات الشأن المعمول بها في الدولة.
                        </li>


                        <li>    يلزم الكفيل بنتثبيت إقامة العاملة خلال فترة لا تتجاوز 55 يومًا من تاريخ استلامها، وفي حال عدم الالتزام، يتم

اسقاط الضمان المقدم له
                        </li>

                  </ol>
            </td>
          </tr>
          
      </tbody></table>


      <table class="table" style="margin-bottom:0;font-weight:bold;font-size:16px;font-family:calibri">
          <tbody><tr style="background: #bf8f00;color: #000;">
            <td align="center" class="bordertop" style="padding:0;">
             <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p1/img_ar_9.png" alt="logo" width="100%">
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
               <p dir="rtl" style="text-align: right;">إذا خالف الطرف الأول الشروط المتفق عليها في هذا العقد يحق للطرف الثاني
                    </p>

                    <p dir="rtl" style="text-align: right;">المطالبة بما يلي</p>
            </td>
          </tr>

      </tbody></table>

      <table class="table" style="margin-bottom:0;font-weight:bold;font-size:16px;font-family:calibri">
          <tbody><tr style="background: #bf8f00;color: #000;">
            <td align="center" class="bordertop" style="padding:0;">
            <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p1/img_ar_10.png" alt="logo" width="100%">
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
          <p dir="rtl" style="text-align: right;"> اذا تأخر الطرف الأول في وضع العامل تحت تصرف صاحب العمل عن ستين يوم من تاريخ استلام اذن الدخول ، يحق لصاحب العمل فسخ العقد واسترداد رسوم الإستقدام ، والرسوم الحكومية . </p>
            </td>
          </tr>

      </tbody></table>

    <table class="table" style="margin-bottom:0;font-weight:bold;font-size:16px;font-family:calibri">
          <tbody><tr style="background: #bf8f00;color: #000;">
            <td align="center" class="bordertop" style="padding:0;">
            <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p1/img_ar_11.png" alt="logo" width="100%">
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
         <ol start="1" dir="rtl" style="line-height: 30px;">

                        <li>
                          .ا   استرداد المتبقي من المبلغ المدفوع لمكتب الإستقدام او بتوفير عامل بديل له حسب اختيار صاحب العمل ، ولا تدخل الرسوم الحكومية في قيمة الاسترداد.
                        </li>
                        <li>
                            يتم اقتطاع المدة التشغيليه من مبلغ الإستقدام اذا اتم العامل شهره الأول.
                        </li>
                        <li>يتحمل مكتب الإستقدام نفقات إعادة العامل الى بلده، اذا تبين او حدث اي مما يلي:</li>
                        <li> انتفاء الكفاءة المهنية وحسن السلوك الشخصي في العامل.</li>

                        <ol dir="rtl" type="i">
                            
                            <li> ثبوت عدم لياقة العامل الصحية.
                            </li>
                            <li>قيام العامل بترك العمل في غير الأحوال المُرخص بها.</li>
                            <li>إنهاء العقد برغبة العامل.</li>

                        </ol>

                    </ol>
            </td>
          </tr>

      </tbody></table>
    
     <table class="table" style="margin-bottom:0;font-weight:bold;font-size:16px;font-family:calibri">
          <tbody><tr style="background: #bf8f00;color: #000;">
            <td align="center" class="bordertop" style="padding:0;">
            <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p1/img_ar_12.png" alt="logo" width="100%">
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
         <ol start="1" dir="rtl" style="line-height: 30px;">

                        <li> .مطالبة مكتب الإستقدام باسترداد جزء من مبلغ الإستقدام إذا لم يثبت للمكتب دور في اي حالة من الحالتين الاتيتين
                        </li>

                        <li> قيام العامل بفسخ العقد، بعد فترة التجربة، وبدون سبب يرجع إلى صاحب العمل</li>
                        <li> قيام العامل، بعد فترة التجربة، بترك العمل لدى صاحب العمل بدون سبب مقبول .
                            </li>
                       

                        <li> مطالبة مكتب الاستقدام باسترداد المتبقي من مبلغ الاستقدام اذا ثبت دور للمكتب في الحالتين( أ ، ب ) من البند رقم 1 من ثالثاً
                        </li>

                    </ol>

            </td>
          </tr>

      </tbody></table>
    
     <table class="table" style="margin-bottom:0;font-weight:bold;font-size:16px;font-family:calibri">
          <tbody><tr style="background: #bf8f00;color: #000;">
            <td align="center" class="bordertop" style="padding:0;">
             <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p1/img_ar_13.png" alt="logo" width="100%">
          </td></tr>

          <tr>
            <td align="right" style="border-top: none;">
         <ol start="1" dir="rtl" style="line-height: 30px;">

                        <li> ( .يتم حساب مبلغ الإسترداد للطرف الثاني عند استرجاع العامل المساعد (أثناء أو بعد فترة التجربة إلى الطرف الأول ، على النحو التالى: (إجمالي تكلفة الاستقدام ÷ مدة عقد عمل العامل بالأشهر) × المدة المتبقية من مدة عقد عمل العامل</li>

                        <li> تعتبر المبالغ المستقطعة من قيمة الإستقدام، هي أتعاب تشغيلية مستحقة الدفع للطرف الأول، بعد اتمام العامل المساعد شهر عمل عند الطرف الثاني
                    </li></ol>

            </td>
          </tr>

      </tbody></table>
     <table class="table" style="margin-bottom:0;font-weight:bold;font-size:16px;font-family:calibri">
          <tbody><tr style="background: #bf8f00;color: #000;">
            <td align="center" class="bordertop" style="padding:0;">
             <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p1/img_ar_14.png" alt="logo" width="100%">
          </td></tr>

          <tr>
            <td align="right" style="border-top: none;">
            <p style="padding-right: 10px;" dir="rtl"> في حال حدوث أي خلاف بين طرفي العقد (صاحب العمل ومكتب الاستقدام) تسري أحكام بشأن عمال الخدمة المساعدة، ولائحته التنفيذية، القانون الاتحادي رقم (9) لسنة2022 وباقي النظم القانونية السارية بوزارة الموارد البشرية والتوطين و القوانين المتبعة في دولة الإمارات العربية المتحدة في هذا الشأن،
                        وتكون محاكم دولة الإمارات هي جهة الاختصاص بنظر أية منازعة متعلقة بهذا العقد</p>

            </td>
          </tr>

      </tbody></table>

    
      

      <table class="table" border="1" style="font-weight:bold;font-size:16px;font-family:calibri">
          <tbody><tr style="background: #bf8f00;color: #000;">
            <td colspan="2" align="center" class="bordertop" style="padding:0;">
           <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p1/img_ar_15.png" alt="logo" width="100%">
            </td>
          </tr>
           <tr>
            <td colspan="2" align="center" class="bordertop">
           <p style="padding-right: 10px;" dir="rtl"> حرر هذا العقد من ثلاث نسخ بعد أن تم توقيعه من الطرفين ، تسلم إحداها الطرف الأول والأخرى الطرف الثاني .وتودع الثالثة لدى الوزارة</p>
            </td>
          </tr>
          <tr>
            <th class="bordertop" style="padding:0;background: #bf8f00;text-align:center"><img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p1/img_ar_17.png" alt="logo" width="100%"></th>
            <th class="bordertop" style="padding:0;background: #bf8f00;text-align:center"><img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p1/img_ar_16.png" alt="logo" width="100%"></th>
          </tr>

          <tr>
            <th style="text-align:right" class="bordertop" width="50%">  Name / الاسم : {{env('company_name') ?? "NA" }} </th>
            <th style="text-align:right" class="bordertop">Name / الاسم  :  {{$conDetails?->customerInfo?->name}}</th>
          </tr>

          <tr>
            <th class="bordertop" style="text-align:right">  التوقيع</th>
            <th class="bordertop" style="text-align:right">التوقيع     <img src="{{$conDetails?->signature}}" style="width: 50%; max-width: 200px;"></th>
          </tr>
          <tr>
            <th colspan="2" class="bordertop" style="text-align:right">Date / التاريخ   :   {{$conDetails?->created_at}}</th>
            
          </tr>
         
      </tbody></table>
    
  </div>
   
</div>





</body>

</html>