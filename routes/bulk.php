<?php

use App\Http\Controllers\Bulk\blkP4;
use App\Http\Controllers\Bulk\blkP4Cntl;
use App\Http\Controllers\Bulk\blkUpCntler;
use App\Http\Controllers\Bulk\blkP1Cntl;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Bulk\blkMaidCntl;
use App\Http\Controllers\Bulk\blkCustomer;
use App\Http\Controllers\Bulk\blkPayroll;


Route::get('/blk-payroll', function () {
    return view('bulk.payroll');
})->name('blk-payroll');


Route::controller(blkPayroll::class)->group(function () {
    Route::post('/payroll/import', 'store')->name('payroll.import');
});

Route::get('/blk-p1', function () {
    return view('bulk.p1');
})->name('blk-p1');

Route::controller(blkP1Cntl::class)->group(function () {
    Route::post('/p1-import', 'import')->name('p1.import');
});



Route::get('/blk-p4', function () {
    return view('bulk.p4');
})->name('blk-p4');

Route::controller(blkP4::class)->group(function () {
    Route::post('/p4-import', 'import')->name('p4.import');
});


Route::controller(blkMaidCntl::class)->group(function () {
    Route::post('/import', 'import')->name('maids.import');
});

Route::get('/blk-maid', function () {
    return view('bulk.maids');
})->name('blk-maid');


Route::controller(blkCustomer::class)->group(function () {
    Route::post('/customer/import', 'import')->name('customers.import');
});

Route::get('/blk-customer', function () {
    return view('bulk.customers');
})->name('blk-customer');



Route::get('/blk-upcoming', function () {
    return view('bulk.up');
})->name('blk-upcoming');

Route::controller(blkUpCntler::class)->group(function () {
    Route::post('/upcoming-import', 'import')->name('upcoming.import');
});


