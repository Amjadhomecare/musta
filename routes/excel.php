<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PendingMaidCostExportController;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\P1ReportBalance;
use App\Exports\P4ReportBalanceStream;
use App\Exports\MaidsNoFilter;
use Illuminate\Http\Request;
use App\Imports\GeneralJournalVoucherImport;

Route::post('/import-general-journal-voucher', function (Request $request) {
    $request->validate([
        'file' => 'required|mimes:xlsx,csv',
    ]);

    try {
        Excel::import(new GeneralJournalVoucherImport, $request->file('file'));
        return response()->json(['message' => 'File imported successfully! yah FARHAN yah khamah yah fakhamah yah dabab '], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    }
})->name('import.general-journal-voucher'); 

Route::get('/page-to-bulk', function () {

    return view ('excel.excel_jv_import');
}
  
)->name('bulk.jv');



Route::get('/p1-report-balance', function () {
    return Excel::download(new P1ReportBalance, 'p1_report_balance.xlsx');
});

Route::get('/p4-report-balance', function () {
    return Excel::download(new P4ReportBalanceStream, 'p4_cus_balance.xlsx');
});


Route::get('/no-filter' , function (){

    return Excel::download(new MaidsNoFilter, 'list_maids_no_filter.xlsx');
});

Route::get('/maid-cost', [PendingMaidCostExportController::class, 'export'])
     ->name('export.maid.cost');