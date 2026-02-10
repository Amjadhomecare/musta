<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\stripe\transactionController;
use App\Http\Controllers\stripe\addStripe;
use App\Http\Controllers\stripe\stripeSub;

Route::middleware(['auth'])->group(function(){


Route::controller(transactionController::class)->group(function(){
    Route::get('/stripe','transactions');
    Route::post('/stripe/sync-charges','syncCharges')->name('stripe.sync-charges');
    Route::get('/async-stripepay','tableAsyncTransactionsT');
    Route::get('/fetch-charges/{stripeID}','getTransactionById');
    Route::post('/stripe/erp-pay','payStripeErp');
  });
  
  
  Route::controller(addStripe::class)->group(function(){
    Route::get('/stripe-links/{name}','pageCustomerStripeLink');
    Route::post('/store-stripe-link','storeStripeLink');
  
  });


    Route::controller(stripeSub::class)->group(function(){
        Route::get('/stripe-subscriptions','listLiveSubscription');
        Route::get('/page-live-sub','pageLiveSubscription');
        Route::post('/async-sub','syncStripeSubscriptions');
        Route::get('/table/stripe-sub','tableSubStripe');
        Route::get('/fetch-sub/{id}','fetchSub');
        Route::post('/sub-update','updateStripe');
         
    });
  
});