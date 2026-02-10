<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Erp\MaidInterviewCntl;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\Erp\SignatureController;


Route::middleware(['auth'])->group(function(){


Route::get('/vue/{vuePage}', function ($vuePage) {
    return view('ERP.vue-page', compact('vuePage'));
})->where('vuePage', '.*');


Route::get('/maidinterview/view', [MaidInterviewCntl::class, 'view'])->name('maidinterview.report');
Route::get('/maidinterview/report', [MaidInterviewCntl::class, 'index']);
Route::get('/signatures/data-table', [SignatureController::class, 'DataTableSign']);
Route::get('/datatable/sms-log', [SmsController::class, 'LogSmsP4']);


});





