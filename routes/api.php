<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Erp\generalJV;
use App\Http\Controllers\Erp\maidsCntl;
use App\Http\Controllers\web\api;
use App\Http\Controllers\web\CustomerUser;
use App\Http\Controllers\Erp\report;
use App\Http\Controllers\stripe\transactionController;
use App\Http\Controllers\stripe\stripeSub;
use App\Http\Controllers\web\audit;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\pro\ApplyVisaController;
use App\Http\Controllers\pro\PaymentOrderCntl;
use App\Http\Controllers\SmsRelayController;
use App\Http\Controllers\Erp\FinanceCntl;
use App\Services\NGeniusService;
use App\Http\Controllers\NGeniusWebhookController;
use App\Http\Controllers\JobController;




/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/




Route::get('/test-ngenius', function (NGeniusService $ngenius) {
    $token = $ngenius->getAccessToken();
    return response()->json(['token' => $token]);
});




Route::post('/sms/send-bulk-cron', [SmsController::class, 'sendBulkSmsForp4']);

Route::post('/webhook/stripe', [StripeWebhookController::class, 'handle']);

Route::post('/ngenius/webhook', [NGeniusWebhookController::class, 'handle']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
   
});

Route::post('/all-jv', [audit::class, 'allJvApi']);


Route::controller(maidsCntl::class)->group(function(){
      Route::get('/attach/maid/test','dataTableMaidAttachment');
    });
  

Route::get('/income-statement', [generalJV::class, 'incomeStatement']);

Route::controller(api::class)->group(function(){
    Route::get('/p4','p4Maids');
    Route::get('/p1','p1Maids');
    Route::get('/p-all','allApprovedMaids');
    Route::get('/outside','outsidCvs');
    Route::get('/sms-p4-cus','messgaeSmsP4');
  
  });

  Route::controller(CustomerUser::class)->group(function(){
    Route::post('/check/customer','checkCustomer');
    Route::post('/profile/invoices','getInvoices');
    Route::post('/profile/c-p1','getP1Contracts');
    Route::post('/customer/complaint','postComplain');

  });



  Route::controller(report::class)->group(function(){
    
    Route::post('/onclick','oneClickReport');
    Route::get('/monthly-report','oneClickReport');
    Route::get('/sms-p4-warning','smsWraningP4');


  
});


Route::controller(transactionController::class)->group(function(){
    
  Route::get('/sstripe','transactions');
 
});


Route::controller(stripeSub::class)->group(function(){
  Route::get('/stripe-subscriptions','listLiveSubscription');
 
});

Route::prefix('apply-visas')->controller(ApplyVisaController::class)->group(function () {
    Route::get('/',       'getApplyVisasList');  
    Route::get('/{id}',   'getApplyVisaById');  
    Route::post('/store', 'storeApplyVisa');
    Route::post('/update','updateApplyVisa');   
    Route::post('/update-status', 'updateStatusAndComment');    

});


Route::prefix('payment-orders')->controller(PaymentOrderCntl::class)->group(function () {
    Route::post('/store', 'storePaymentOrder'); 
});


Route::post('/relay/sms', [SmsRelayController::class, 'send']); 
Route::post('/relay/sms-bulk', [SmsRelayController::class, 'sendBulk']); 


Route::get('/accounting/comparative-trial-3m', [FinanceCntl::class, 'comparativeTrial3MonthEndsApi'])
    ->name('api.accounting.comparative-trial-3m');


// Job dispatch endpoints - trigger jobs to run in background queue
Route::prefix('jobs')->controller(JobController::class)->group(function () {
    Route::get('/', 'listJobs');
    Route::post('/process-dd-followups', 'processDdFollowUps');
    Route::post('/reset-booked-maids', 'resetBookedMaids');
    Route::post('/run-daily-recursions', 'runDailyRecursions');
    Route::post('/send-comparative-trial', 'sendComparativeTrial');
    Route::post('/send-daily-onclick-report', 'sendDailyOnclickReport');
    Route::post('/send-income-statement', 'sendIncomeStatement');
    Route::post('/send-monthly-onclick-report', 'sendMonthlyOnclickReport');
});