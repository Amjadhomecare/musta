<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Backend\EmployeeController;
use App\Http\Controllers\Backend\CustomerController;
use App\Http\Controllers\Backend\SupplierController;
use App\Http\Controllers\Backend\SalaryController;
use App\Http\Controllers\Backend\AttendenceController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\ExpenseController;
use App\Http\Controllers\Backend\PosController;
use App\Http\Controllers\Backend\OrderController;
use App\Http\Controllers\Erp\generalJV;
use App\Http\Controllers\Erp\typingCntl;
use App\Http\Controllers\Erp\classCategory4Cntl;
use App\Http\Controllers\Erp\maidsPayrollCntl;
use App\Http\Controllers\Erp\maidsCntl;
use App\Http\Controllers\Erp\addReceiveCreditNoteCntl;
use App\Http\Controllers\Erp\categoryOneClass;
use App\Http\Controllers\Erp\customerCntl;
use App\Http\Controllers\Erp\complainCntl;
use App\Http\Controllers\Erp\complainDepCntl;
use App\Http\Controllers\Erp\invoiceCntl;
use App\Http\Controllers\Erp\maidReportCntl;
use App\Http\Controllers\Erp\customerReportCntl;
use App\Http\Controllers\Erp\report;
use App\Http\Controllers\Erp\super_admin;
use App\Http\Controllers\Erp\installmentCntl;
use App\Http\Controllers\Erp\cashierCntl;
use App\Http\Controllers\Erp\BulkJVController;
use App\Http\Controllers\Erp\p4Audit;
use App\Http\Controllers\Erp\customerAdvanceCntl;
use App\Http\Controllers\Erp\hrCntl;
use App\Http\Controllers\Erp\customerHcAndFc;
use App\Http\Controllers\Erp\AuditDhCntl;
use App\Http\Controllers\Erp\NewPayRoll;
use App\Http\Controllers\Erp\intreviewCntl;
use App\Http\Controllers\website\HomePageCntl;
use App\Http\Controllers\Erp\UploadDocument;
use App\Http\Controllers\Erp\SignatureController;
use App\Http\Controllers\OcrController;
use App\Http\Controllers\JournalExportController;
use App\Http\Controllers\DirectDebitController;
use App\Http\Controllers\Erp\FinanceCntl;
use App\Models\TrainingInv;
use App\Http\Controllers\pro\PaymentOrderCntl;
use App\Http\Controllers\pro\ApplyVisaController;
use App\Http\Controllers\SmsCountryController;
use App\Http\Controllers\AccountingRecursionController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\SurveyMaidController;
use App\Http\Controllers\NetWorkLinkController;
use App\Http\Controllers\CancelationRequestCntl;
use App\Http\Controllers\BlacklistController;
use App\Http\Controllers\Bulk\MaidDocExpiryController;
use App\Http\Controllers\DebitCancellationController;









Route::get('/test-mail', function () {
    try {
        Mail::raw('This is a test email from Laravel 12.', function ($message) {
            $message->to('ameeram4@gmail.com')
                    ->subject('Laravel Test Email');
        });
        return '✅ Test email sent successfully!';
    } catch (\Exception $e) {
        // Also check storage/logs/laravel.log for full trace
        return '❌ Error sending email: ' . $e->getMessage();
    }
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::get('/dashboard', function () {
    return view('ERP.customers.allCustomers');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('/admin/logout', [AdminController::class, 'AdminDestroy'])->name('admin.logout');

Route::get('/logout', [AdminController::class, 'AdminLogoutPage'])->name('admin.logout.page');



Route::post('/bulk/maid-doc-expiry/import', [MaidDocExpiryController::class, 'import'])
    ->name('bulk.maid-doc-expiry.import');

Route::post('/bulk/passport-doc-expiry/import', [MaidDocExpiryController::class, 'importPassportExpiry'])
    ->name('bulk.passport-doc-expiry.import');



Route::get('/blacklist/approve/{id}', [BlacklistController::class, 'showApprovalForm'])->name('blacklist.approve');
Route::post('/blacklist/approve/{id}', [BlacklistController::class, 'processApproval'])->name('blacklist.process');
Route::get('/blacklist/success', [BlacklistController::class, 'success'])->name('blacklist.success');


Route::middleware(['auth'])->group(function(){

Route::get('/', function () {
    return view('welcome');
});


Route::get('/debit-cancellation', [DebitCancellationController::class, 'index'])->name('debit.cancellation.index');

Route::get('/cancelation-request/{name}', [CancelationRequestCntl::class, 'CancelationRequestList'])->name('cancelation.request.list');
Route::post('/cancelation-request/store', [CancelationRequestCntl::class, 'storeCancellationRequest'])->name('cancelation.request.store');


Route::post('/netlink', [NetWorkLinkController::class, 'store']);
Route::get('/netlink',  [NetWorkLinkController::class, 'index']);
Route::put('/netlink/{id}', [NetWorkLinkController::class, 'update']);
Route::post('/netlink/{id}/refresh-status', [NetWorkLinkController::class, 'refreshOrderStatus']);
Route::post('/netlink/{id}/refund', [NetWorkLinkController::class, 'processRefund']);


Route::prefix('api/wiredtransfer')->controller(App\Http\Controllers\WiredTranserCntl::class)->group(function () {
    Route::get('/',        'index');   // List with pagination
    Route::post('/',       'store');   // Create
    Route::put('/{id}',    'update');  // Update
    Route::delete('/{id}', 'destroy'); // Delete
});


Route::get('/admin/profile', [AdminController::class, 'AdminProfile'])->name('admin.profile');

Route::post('/admin/profile/store', [AdminController::class, 'AdminProfileStore'])->name('admin.profile.store');

Route::get('/change/password', [AdminController::class, 'ChangePassword'])->name('change.password');

Route::post('/update/password', [AdminController::class, 'UpdatePassword'])->name('update.password');


Route::get('/chat', function () {
    return view('ERP.realtime.public_chat');
});



    Route::prefix('accounting')->group(function () {
        Route::get('recursions', [AccountingRecursionController::class, 'index']);
        Route::post('recursions', [AccountingRecursionController::class, 'store']);
        Route::post('recursions/update', [AccountingRecursionController::class, 'update']);
        Route::delete('recursions/{id?}', [AccountingRecursionController::class, 'destroy']);
    });

    Route::prefix('report-recipients')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReportRecipientController::class, 'index'])->name('report-recipients.index');
        Route::post('/', [\App\Http\Controllers\ReportRecipientController::class, 'store'])->name('report-recipients.store');
        Route::post('/{recipient}/toggle', [\App\Http\Controllers\ReportRecipientController::class, 'toggle'])->name('report-recipients.toggle');
        Route::delete('/{recipient}', [\App\Http\Controllers\ReportRecipientController::class, 'destroy'])->name('report-recipients.destroy');
    });


Route::post('/sms/send', [SmsCountryController::class, 'send']);

Route::prefix('payment-orders')->controller(PaymentOrderCntl::class)->group(function () {
    Route::get('/', 'getPaymentOrdersList');     
    Route::post('/store-or-update', 'storeOrUpdatePaymentOrder'); 
    Route::post('/bulk-approve','bulkApprove');

});

Route::prefix('apply-visas')->controller(ApplyVisaController::class)->group(function () {
    Route::get('/',       'getApplyVisasList');  
    Route::get('/{id}',   'getApplyVisaById'); 
    Route::post('/store', 'storeApplyVisa');
    Route::post('/update','updateApplyVisa');        
    Route::delete('/{id}/documents/{index}', 'deleteDocument')
        ->name('applyVisas.deleteDoc');
});




Route::get('/erp/training/invoice/{id}', function ($id) {
    $invoice = TrainingInv::findOrFail($id);

    $company = [
        'name' => 'Home Care Plus Consultancy LLC',
        'address' => 'Villa 464 Al Wasl Rd - Jumeirah - Jumeirah 2 - Dubai',
        'phone' => '+971 56 684 7766',
        'email' => 'info@homecareformaids.com'
    ];

    return view('ERP.training.inv', compact('invoice', 'company'));
})->name('erp.training.invoice.print');

Route::get('/direct-debit-list', [DirectDebitController::class, 'directDebitList']);
Route::get('/dd-follow-ups', [DirectDebitController::class, 'ddFollowUpList']);

// DD Follow-Up pages
Route::get('/dd-followup/manual', [\App\Http\Controllers\DdFollowUpController::class, 'manualList']);
Route::get('/dd-followup/replied', [\App\Http\Controllers\DdFollowUpController::class, 'repliedList']);
Route::get('/dd-followup/pending', [\App\Http\Controllers\DdFollowUpController::class, 'pendingList']);
Route::get('/dd-followup/rejected', [\App\Http\Controllers\DdFollowUpController::class, 'RejectedDdNotSignure']);
Route::post('/dd-followup/update-signatures', [App\Http\Controllers\DdFollowUpController::class, 'updateSignatures']);
Route::post('/store-direct-debit', [DirectDebitController::class, 'storeDirectDebit']);
Route::post('/direct-debits/upload-file', [DirectDebitController::class, 'uploadFile']);
Route::delete('/direct-debit/{id}', [DirectDebitController::class, 'delete']);
Route::post('/direct-debit/send-sms', [DirectDebitController::class, 'sendSigningLink']);
Route::get('/direct-debit/by-ref/{ref}', [DirectDebitController::class, 'getByRef']);
Route::post('/request-cancellation', [DirectDebitController::class, 'requestCancellation']);

// Refund List API
Route::get('/api/refundlist', [CancelationRequestCntl::class, 'getRefundListApi']);
Route::post('/api/refundlist/bulk-approve', [CancelationRequestCntl::class, 'bulkApproveRefunds']);
Route::get('/cancelation-requests', [DirectDebitController::class, 'cancelationRequestList']);

Route::get('/export-journal', [JournalExportController::class, 'export'])->name('export.journal');


Route::post('/ocr/azure', [OcrController::class, 'analyze']);



Route::post('/erp/signatures', [SignatureController::class, 'store'])
     ->name('erp.signatures.store');



Route::controller(FinanceCntl::class)->group(function () {
    Route::get('/customer-balances', 'customerBalances');
      Route::get('/comparative-trial', 'comparativeTrial')->name('erp.comparative-trial');
});



// Upload Document
Route::controller(UploadDocument::class)->group(function(){
  Route::get('/upload-document', 'index')->name('upload.document');
  Route::post('/upload-document/store', 'store')->name('upload.document.store');
  Route::post('/upload-document/delete', 'destroy')->name('upload.document.delete');

});



// website
Route::middleware('seo.group')->controller(HomePageCntl::class)->group(function () {
  Route::get('/website', 'index')->name('website.index');
  Route::post('/website/update', 'update')->name('homepage.update');
});



// Interview Routes
Route::controller(IntreviewCntl::class)->group(function() {
  Route::get('/intreview', 'index')->name('intreview.index');
  Route::get('/table-interview', 'tableList')->name('intreview.table');
  Route::post('/store-interview', 'store')->name('intreview.store');
  Route::get('/interview/{id}', 'getInterviewById')->name('intreview.get'); 
  Route::post('/update-interview', 'update')->name('intreview.update'); 
});


// New Payroll Version
Route::controller(NewPayRoll::class)->group(function(){
  Route::get('/payroll','index')->name('payroll.index');
  Route::get('/get-payroll','getPayRoll')->name('getPayRoll');
  Route::post('/store-payroll','newbulkPaid')->name('storePayRoll');
});

///Auditing Direct hire
Route::controller(AuditDhCntl::class)->group(function(){
  Route::get('/dh-audit','index')->name('page.audi.dh');
  Route::get('/get-audit-dh','getListAuditDh')->name('getListAuditDh');
  
 });



///General Journal Voucher For Accounting
Route::controller(generalJV::class)->group(function(){
  
    Route::get('/jvlog', 'index')->name('jvlog.index');
    Route::get('/all-jv','AllRegistredGeneralJVCntl')->name('AllRegistredGeneralJVCntl') ;
    Route::get('/view/jv/selected/{refnumber}','viewSelectedJournalEntryGroupByRefNumber')->name('viewSelectedJournalEntryGroupByRefNumber');
    Route::get('/view/jv/edit/{refnumberedit}','editSelectedJournalEntryGroupByRefNumber')->name('editSelectedJournalEntryGroupByRefNumber');
    Route::post('/jv/update','updateSelectedJournalEntryGroupByRefNumberAction')->name('updateSelectedJournalEntryGroupByRefNumberAction');

    Route::get('/preconnection/jv','viewPreConnectionGeneralJVCntl')->name('viewPreConnectionGeneralJVCntl');
    Route::get('/pre_connection/accounting','viewPreConnectionAccounting')->name('viewPreConnectionAccounting');
    Route::post('add/connection/jv','addNewPreConnectionGeneralJVCntl')->name('addNewPreConnectionGeneralJVCntl');
 
    Route::get('account/statment','showAccountLedgerStatmentCtrl')->name('showAccountLedgerStatmentCtrl');
    Route::get('add/new/ledger','viewRegisterNewLedgerCntl')->name('viewRegisterNewLedgerCntl');
    Route::post('add/new/ledger','storeRegisterNewLedgerCntl')->name('storeRegisterNewLedgerCntl');
    Route::get('trial','viewTrialBalanceCntl')->name('viewTrialBalanceCntl');
    Route::get('invoices/connection','viewInvoicesPreConnectionsCntl')->name('viewInvoicesPreConnectionsCntl');
    Route::post('add/invoice/connection','storeInvoicesPreConnectionsCntl')->name('storeInvoicesPreConnectionsCntl');
    Route::get('ajax-list-ledgers','AjaxlistAccountLedgers')->name('AjaxlistAccountLedgers');
    Route::get('/ledger/{id}','editLedger')->name('editLedger');
    Route::post('/update-ledger','updateLedger')->name('updateLedger');
    Route::get('ajax-list-invoice-connection','AjaxlistInvoiceConnection')->name('AjaxlistInvoiceConnection');
    Route::get('/invoice-connection-edit/{name}','checkInvoiceConnection')->name('checkInvoiceConnection');
    Route::post('/update-invoice-connection','updateConnectionForInvoice')->name('updateConnectionForInvoice');
    Route::get('/delete-invoice-connection/{name}','deleteInvConnetion')->name('deleteInvConnetion');
    Route::get('list-pre-connection' , 'listConnectionJv')->name('listConnectionJv');
   
    Route::get('ajax-list-pre-connection','AjaxlistPreConnectionAcc')->name('AjaxlistPreConnectionAcc');
    Route::get('/pre-connection-edit/{name}','checkPreConnectionAcc')->name('checkPreConnectionAcc');
    Route::post('/update-connection','updateConnection')->name('updateConnection');
    Route::get('/delete-jv-connection/{name}','deleteJvConnetion')->name('deleteJvConnetion');


    Route::get('/view_income_statment' ,'viewPagePandL')->name('viewPagePandL');

    Route::get('/balance-sheet' ,'balanceSheet')->name('balance-sheet');


   });



   Route::middleware(['auth', 'accounting'])->controller(generalJV::class)->group(function(){

        Route::post('/jv','addNewGeneralJVCntl')->name('addNewGeneralJVCntl');
        Route::get('all/general/jv','viewAllRegistredGeneralJVCntl')->name('viewAllRegistredGeneralJVCntl');
        Route::get('all-ledger','listOfLedgers')->name('listOfLedgers');
        Route::get('/search/ledger','viewSearchStatmentAccountCntl')->name('viewSearchStatmentAccountCntl');
   });




   ///add receive credit notes all routes
Route::controller(addReceiveCreditNoteCntl::class)->group(function(){
  Route::post('/receive-payment','receivedFromCntl')->name('receivedFromCntl');
  Route::post('/get-credit-note-data','getCreditNoteData')->name('getCreditNoteData');
  Route::post('/store-credit-note-data','creditNoteFromTypingCntl')->name('creditNoteFromTypingCntl');
  Route::get('/receipt/voucher/cat1/{id}','viewReceiptVoucherCat1')->name('viewReceiptVoucherCat1');
  //Route For Category one
  Route::post('/maids-payment','receivedFromMaidsSalesCntl')->name('receivedFromMaidsSalesCntl');
  Route::post('/credit-note-maidssales','creditNoteFromMaidsSalesCntl')->name('creditNoteFromMaidsSalesCntl');
  //Route For typing
  Route::get('/receipt/voucher/typing/{id}','viewReceiptVoucherTyping')->name('viewReceiptVoucherTyping');
  //For cashier
  Route::get('/cashier','cashierReceiptVoucher')->name('cashierReceiptVoucher');
  Route::get('ajax-cashier/{contractRef}','getContractDetailsForReceivePayment')->name('getContractDetailsForReceivePayment');
  Route::get('ajax-inv4/{invRef4}','getInvoice4RefDetailsForReceivePayment')->name('getInvoice4RefDetailsForReceivePayment');
  Route::post('/store-rv','storeCashierRV')->name('storeCashierRV');
  Route::get('ajax-receipt-vousher','getAllRV')->name('getAllRV');
  Route::get('/receipt/voucher/cashier/{id}','viewReceiptVoucherCashier')->name('viewReceiptVoucherCashier');
  // apply credit
  Route::post('/apply-credit','applyCredit')->name('applyCredit');
  Route::post('/apply-credit-bulk','applyCreditBulk')->name('applyCreditBulk');
 });


    ///typing all route
    Route::controller(typingCntl::class)->group(function(){

      Route::get('typing/all/invoices','viewAllTypingInvoicesCntl');
      Route::get('/typing/selected/invoice/{typing_ref}','viewSingleTypingInvoicesCntl')->name('viewSingleTypingInvoicesCntl');
      Route::get('/typing-invoices','ajaxAllTypingInvoicesCntl')->name('ajaxAllTypingInvoicesCntl');
      Route::get('invoice/typing/{refCode}','viewTypingInvoice')->name('viewTypingInvoice');
      Route::post('add/invoice/typing/testing','saveTypingInvoice')->name('saveTypingInvoice');
      //for ajax select twos
      Route::get('list-invoice-preconnection' , 'listConnectionInvoice')->name('listConnectionInvoice');


    });



   // All route for Category 4
   Route::controller(classCategory4Cntl::class)->group(function(){
     Route::get('/category/4','viewAddingCategory4Contract')->name('viewAddingCategory4Contract');
     Route::post('add/new/category/4','storeCategory4ContractCntl')->name('storeCategory4ContractCntl');
     Route::get('/category4/selecting/date','viewCategory4UpcomingCntl')->name('viewCategory4UpcomingCntl');
     Route::get('/category4/selected_date','viewAccruedDateCat4Cntl')->name('viewAccruedDateCat4Cntl');
     Route::get('all/category4/contract','viewAllCat4Cntl')->name('viewAllCat4Cntl');
     Route::get('category4/data', 'getCategory4Contracts')->name('category4.data');
     Route::get('ajax-invoices-cat4','getCategory4Invoics')->name('getCategory4Invoics');
     Route::delete('/delete_sign/p4/{id}','deletSignP4')->name('deletSignP4');
     Route::get('category4/invoices','viewAllInvoicesCat4')->name('viewAllInvoicesCat4');
     Route::get('/p4/get/date/{id}','getById')->name('getById');
     Route::post('/update/date/p4','updateById');
    

  

   });


   // All Maids Route
   Route::controller(maidsCntl::class)->group(function(){
    Route::get('/index/maid' , 'maidPage');
    Route::get('all/maids','viewMaidsCVCntl')->name('viewMaidsCVCntl');
    Route::post('/add-maid', 'storeMaidsCvCntl')->name('storeMaidsCvCntl');
    Route::get('/all-maids','getAllMaids')->name('getAllMaids');
    Route::post('/update-maid-cv', 'editMaidCv')->name('editMaidCv');
    Route::get('/cv/{id}','viewMaidCv')->name('viewMaidCv');
    Route::post('/book/maid','bookMaid')->name('bookMaid');
    Route::post('/update/video','updateMaidLink')->name('updateMaidLink');
    Route::post('/update/status','updateMaidStatus')->name('updateMaidStatus');
    Route::get('/maid/{id}','getMaidById')->name('getMaidById');
    Route::get('/attach/maid','pageMaidAttachment')->name('pageMaidAttachment');
    Route::post('/maids/upload-attachment','uploadMaidAttachment')->name('uploadMaidAttachment');
    Route::get('/table/attach/maid','dataTableMaidAttachment')->name('dataTableMaidAttachment');
    Route::post('/maids/delete-attachment','deleteMaidAttachment')->name('deleteMaidAttachment');
    Route::get('/maid-filter/{maidId}','showFilter');
    Route::post('/filter-update','updateOrCreateFilter');
    Route::get('/doc-expire/{id}','getMaidWithDocExpiry');

  });

  Route::controller(maidReportCntl::class)->group(function(){
    Route::post('/attach/maid/doc-expiry','addMaidDocExpiry');
    Route::get('/maid-report/{name}', 'maidReport')->name('maidReport');
    Route::get('/p1/contract/maid/{name}','p1ContractsMaid');
    Route::get('/maid-doc-expiry/{id}','showMaidDocExpiry');
    Route::get('/payroll/history/{name}','maidPayRollHistory');
    Route::get('/page/maid-finance/{name}','pageMaidFinance');
    Route::get('/maid-finance/{name}', 'maidFinanceReport');
    Route::get('/maid-report/p4/{name}','maidReportP4');
    Route::get('/p4/contracts/maid/{name}','p4ContractMaids');
    Route::get('/page/maid/invoices/{name}','pageMaidInvoice');
    Route::get('/maid/invoices/{name}','maidInvoices');
    Route::get('/payroll-note/{name}','maidAdvanceOrDeduction');
    Route::get('/dedction-maid/{name}','dataTableAdvanceOrDedction');
    Route::post('/jv/maid','makeJV');
    Route::get('/doc/maid/{name}','maidDocumentReport')->name('maidDocumentReport');
    Route::get('/pro/p4/{name}','ProSteps');
    Route::get('/pl/{name}','POrL');
   
  });




        //  Payrolls For Maids
  Route::controller(maidsPayrollCntl::class)->group(function(){
          Route::get('select/month/payroll','selectPayrollMonthCntl')->name('selectPayrollMonthCntl');
          Route::get('maids/payrolls','getMaidsSalariesPayRollsForCat4MaidsCntl')->name('getMaidsSalariesPayRollsForCat4MaidsCntl');
          Route::get('adcance/deduction/payrolls','viewFormAdvanceAndDeductionCntl')->name('viewFormAdvanceAndDeductionCntl');
          Route::post('add/new/adcance/deduction/payrolls','storeAdvanceOrDeductionCntl')->name('storeAdvanceOrDeductionCntl');
          Route::post('/update-advance','updateAdvanceAndDeductionCntl')->name('updateAdvanceAndDeductionCntl');
          Route::post('/bulk-paid','bulkPaid')->name('bulkPaid');
          Route::get('/calculate-closing-balance/{customerId}','calculateClosingBalance');
          Route::get('/all/paidpayroll' ,'viwePaidMaids')->name('viwePaidMaids');
          Route::get('/ajax/all/paid-payroll' ,'ajaxAllPaidMaidPayroll')->name('ajaxAllPaidMaidPayroll');
          Route::get('/data-advance','dataTableAdvancePayroll');
          Route::delete('/delete-payroll/{id}','deletePaidMaidPayroll');
          Route::post('/store-new','storeAdvanceAndDeductionCntl');


        });

        // Category one
   Route::controller(categoryOneClass::class)->group(function(){
    Route::get('form-cat1','viewAddingCategory1Contract')->name('viewAddingCategory1Contract');
    Route::post('store-contract','storeCateOneContract')->name('storeCateOneContract');
    Route::get('view-cat1','viewAllCat1Cntl')->name('viewAllCat1Cntl');
    Route::get('ajax-cat1','getCategory1Contracts')->name('view.cat1');
    Route::get('invoices-cat1','categoryOneInvoicesList')->name('categoryOneInvoicesList');
    Route::get('ajax-invoices-cat1','getCategory1Invoics')->name('getCategory1Invoics');
    Route::delete('delete_sign/cat1/{id}','deletSignCat1')->name('deletSignCat1');

  });



         //Customers Route
   Route::controller(customerCntl::class)->group(function(){
            Route::get('all-customers','listOfcustomers')->name('listOfcustomers');
            Route::get('ajax-customers-list','getAllCustomers')->name('getAllCustomers');
            Route::post('save-customer','saveCustomer')->name('saveCustomer');
            Route::PATCH('update_customer','update')->name('updateCustomer');
            Route::get('/attach/customer','pageCustomerAttachment')->name('pageCustomerAttachment');
            Route::post('/customer/upload-attachment','uploadCustomerAttachment')->name('uploadCustomerAttachment');  
      
      
   });


      //Customers Report
   Route::controller(customerReportCntl::class)->group(function(){
            
        Route::get('/customer/report/{name}','getCustomerp1Report')->name('getCustomerp1Report');
        Route::get('/cont/p1/{name}','getContractsTableCustomerReport');
        Route::get('/customer/report/p4/{name}','getCustomerp4Report')->name('getCustomerp4Report');
        Route::get('/p4/customer/{name}','p4ContractReport');
        Route::get('/customer/soa/{name}','getCustomerSOA');
        Route::get('/soa/customer/{name}','soa');
        Route::get('/page/invoices/{name}','pageCustomerInvoice');
        Route::get('/customer/invoices/{name}','customerInvoices');
        Route::get('/page/customer/attachment/{name}','pagecustomerAttachment');
        Route::get('customer/attach/{name}','tableAttachment');
        Route::get('/customer/make/p1/{name}','pageMakeP1');
        Route::get('/customer/make/p4/{name}','pageMakeP4');
        Route::get('//installment-p4/{name}','pageInstallment');
        Route::get('/installment-p4-make/{name}','datatableinstallment');
        Route::get('/adv-customer/{name}','tableCustomerAdv');    
        Route::post('/customer/jv','makeCustomerJV');

        Route::get('cus-comp/{name}','pageCusComplaint');
        Route::get('cus-comp-list/{name}','tableComplaint');
        Route::post('/p1-update','p1Update');

     });

          //complain  And Arrival
    Route::controller(complainCntl::class)->group(function(){
            Route::post('/passport/update', 'updatePassportStatus')->name('passport.update');

            Route::post('add/return/action','storeMaidReturnRecordCntl')->name('storeMaidReturnRecordCntl');
            Route::get('credit/memo/form','formCreditMemo')->name('formCreditMemo');
            Route::get('ajax-cat1/{contractRef}','getContractDetailsCat1')->name('getContractDetailsCat1');
            Route::post('ajax-store-credit-memo','storeCreditMemo')->name('storeCreditMemo');
            Route::get('ajax-credit-memo-list','getAllCreditMemo')->name('getAllCreditMemo');
            Route::get('/pdf-credit-memo-list/{id}','generateCreditMemoPDF')->name('generateCreditMemoPDF');
            Route::get('/arrival','arrivalList')->name('arrivalList');
            Route::get('ajax-maid/{name}','ajaxGetMaidInfo')->name('ajaxGetMaidInfo');
            Route::post('save-arrival','storeMaidArrive')->name('storeMaidArrive');
            Route::get('approving-maid','pendingArrivalList')->name('pendingArrivalList');
            Route::get('ajax-arrival-list','getPendingArrivalForApproving')->name('getPendingArrivalForApproving');
            Route::post('ajax-approve-maid','updateMaidToApprove')->name('updateMaidToApprove');
            Route::get('ajax-cat4/{contractRef}','getContractDetailsCat4')->name('getContractDetailsCat4');
            Route::get('return-list-cat4','listReturnCat4')->name('listReturnCat4');
            Route::post('/ajax-cat1-return','storeMaidReturnCat1RecordCntl')->name('storeMaidReturnCat1RecordCntl');
            Route::get('ajax-return-list-cat4','ajaxListReturnCat4')->name('ajaxListReturnCat4');
            Route::post('ajax-approve-return4','bulkUpdateApprovalReturnCat4')->name('bulkUpdateApprovalReturnCat4');
            Route::get('return-list-cat1','listReturnCat1')->name('listReturnCat1');
            Route::get('ajax-return-list-cat1','ajaxListReturnCat1')->name('ajaxListReturnCat1');
            Route::post('ajax-approve-return1','bulkUpdateApprovalReturnCat1')->name('bulkUpdateApprovalReturnCat1');
            Route::get('page/maid/release','pageReleaseCv')->name('pageReleaseCv');
            Route::post('store/releases','storeMaidRelease')->name('storeMaidRelease');
            Route::get('/page/pending/release','pendingReleaseList')->name('pendingReleaseList');
            Route::get('/ajax-release-list','getPendingReleaseForApproving')->name('getPendingReleaseForApproving');
            Route::post('/ajax-release-maid','updateMaidToReleased')->name('updateMaidToReleased');
            Route::get('/list-release','getReleases')->name('getReleases');
            Route::delete('/delete-release/{id}','deleteMaidRelease')->name('deleteMaidRelease');
            Route::get('/arrival-list','getArrival')->name('getArrival');
            Route::delete('/delete-arrival/{id}','deletePendingArrival')->name('deletePendingArrival');
            Route::get('/ministry-return/{p1}','ministry_return');


        });


      //invoiceCntl no contract invoice
  Route::controller(invoiceCntl::class)->group(function(){
      Route::get('/inv/{id}','selectInvoiceId');
      Route::post('/store-invoice' , 'storeNoContractInvoiceCntl')->name('storeNoContractInvoiceCntl');
      Route::get('/ajax/list/invoices','ajaxAllListInvoice')->name('ajaxAllListInvoice');
      Route::get('/no-contract-invoice/{refCode}','viewNoContractInvoice')->name('viewNoContractInvoice');
      Route::get('/maid-search','serchMaid')->name('maid.search');
      Route::get('/list/non/contract','non_contract_invoice');
      Route::get('list-invoice-preconnection-non-contract' , 'listConnectionInvoiceNonContract')->name('listConnectionInvoiceNonContract');
    
     });


  Route::controller(report::class)->group(function(){
    
    Route::post('/fetch','fetch_report')->name('fetch_report');
    Route::get('/fetch/cash/equivalent','fetch_cash_equivalent')->name('fetch_cash_equivalent');
    Route::get('/fetch/past/three/typing','income_last_three_months_typing')->name('income_last_three_months_typing');
    Route::get('/fetch/past/three/p1','income_last_three_months_package1')->name('income_last_three_months_package1');
    Route::get('/fetch/past/three/p4','income_last_three_months_package4')->name('income_last_three_months_package4');
    Route::get('/onclick-report','oneClickReport');
    Route::get('/page-report','pageReport');
    Route::get('/dynamic-report','pageDynamicReport' );
    Route::get('/log-book','logChecking');
    Route::get('/table-log','tableLog');
    Route::get('/table-wrost-p4','tableWorsMaidsLastMonth');
   
});


Route::controller(super_admin::class)->group(function(){

  Route::get('/add/user','view_add_user')->name('view_add_user');
  Route::post('/post/user','add_new_user')->name('add_new_user');
  Route::get('/fetch/users','getAllusers')->name('getAllusers');
  Route::post('/update/user','updateUser')->name('updateUser');

});

Route::controller(complainDepCntl::class)->group(function(){
  Route::post('/post/complaint','postNewComplaint');
  Route::post('/post/accounting-complain','storeAccountingCOmplain');
  Route::get('/page/accounting-complain','pageRegesterComplaint');
  Route::get('/searching-user','searchingUser');
  Route::get('/all/notified/complaints','tableNotification');
  Route::get('/get/notify/{id}','fetchNotify');
  Route::post('/update/notify','updateNotify');
  Route::delete('/delete/notify/{id}','deleteComplaint');
  Route::get('/all/notified/complaints-by-user','tableNotificationByuser');


});

  
Route::controller(installmentCntl::class)->group(function(){
           Route::get('/page/installment','pageInstallment')->name('pageInstallment');
           Route::get('/table/installment','datatableinstallment');
           Route::post('/installment/store','storeInstallmentInvoice');
             Route::post('/installment/bulk-store',   'bulkStoreInstallments')            
         ->name('installment.bulkStore');
           Route::get('/installment/{id}','getById');
           Route::post('/store/customize','storeCustomized');
           Route::post('update/contract/cat4','updateUpcomingInstallments')->name('update-installments');
           Route::get('delete/upcoming/{id}','deleteUpcomingInstallment')->name('deleteUpcomingInstallment');
           Route::get('copy/upcoming/{id}','CopyUpcomingInstallmentCntl')->name('CopyUpcomingInstallmentCntl');
           Route::post('join/new/maid','joinNewMaidCategory4ContractCntl')->name('joinNewMaidCategory4ContractCntl');
           Route::get('/edit-upcoming-installment/{ref_contract}','EditUpcomingInstallmentCntl')->name('EditUpcomingInstallmentCntl');

          });
         

          Route::controller(cashierCntl::class)->group(function(){
                   
            Route::get('/page/cashier','pageCashir')->name('pageCashir');   
            Route::get('/table/rv','dataTableRv')->name('dataTableRv'); 
            Route::post('/store/receipt','storeRv')->name('storeRv');  

            Route::get('receipt/{num}','showRV')->name('showRV'); 
              
 
           });

              /// bulk jv
Route::controller(BulkJVController::class)->group(function(){
  Route::get('/bulk','pageUpload')->name('pageUpload');
  Route::post('/bulk-jv/upload', 'upload')->name('bulk_jv.upload');
  Route::get('/page/jv/bulk' ,'pageTableJvBulk')->name('pageTableJvBulk');
  Route::get('/table/jv/bulk' , 'tableJvBulk')->name('tableJvBulk');
  Route::get('/get/jv/{ref}' , 'getTran');
  Route::post('/store/bulk', 'storeBulk')->name('storeBulk');
  Route::post('/bulk-delete', 'deleteBulkJv');

 });
      


 Route::controller(p4Audit::class)->group(function(){
     Route::get('/p4-audit','p4Audit');
 });

 Route::controller(customerAdvanceCntl::class)->group(function(){
    Route::get('ar-ads','pageCustomerAdvance');
    Route::get('/table-ads','tableCustomerAdvance')->name('tableCustomerAdvance');
    Route::get('/ads/{id}','getAdvanceDetails')->name('getAdvanceDetails');
    Route::post('/store-ar-ads','storeAdvance')->name('storeAdvance');
    Route::post('/rv-advance','storeRvAdvance')->name('storeRvAdvance');

});

Route::controller(hrCntl::class)->group(function(){
  Route::get('/table-p4','tableMaidExpairP4');
  Route::post('/store-leave-salary','storeLevaeSalary');
  Route::put('/leave-salaries/{id}', 'storeLevaeSalary'); 
  Route::get('/leave-salaries','leaveSalaryMaidList');
  Route::get('/maid-clearence/{id}','getMAidClearenceById')->name('pageMaidClearence');
  Route::post('/maid-clearance/{id}/update-items','updateClearanceItems')->name('clearance.update_items');
  Route::post('/maid-clearance/{id}/save-signatures','saveClearanceSignatures')->name('clearance.save_signatures');
  Route::post('/store-staff-leave-salary','leaveSalaryStaffForm');
  Route::put('/staff-leave-salaries/{id}', 'leaveSalaryStaffForm');
  Route::get('/leave-salaries-staff','leaveSalaryStaffList');    
  Route::get('/staff-clearence/{id}','getStaffClearenceById')->name('pageStaffClearence');
  Route::get('/maid-visit-visa','getMaidVistVisa')->name('pageMaidVisitVisa');
  Route::put('/bulk-update-maid-visit-visa','bulkUpdateMaidVisitVisa');
  Route::post('/store-or-update-ticket','storeOrUpdateTicket');
  Route::put('/ticket-maid/{id}','storeOrUpdateTicket');
  Route::get('/ticket-maid-list','getTicketMaidList');
  Route::get('/get-maids-salary-p1','getMaidsSalaryP1');
  Route::get('/get-maids-salary-p1-by-name/{name}','getMaidsSalaryP1ByName');
  Route::get('/noc-list','NocList');
  Route::post('/store-noc','storeNoc');
  Route::put('/store-noc/{id}','storeNoc');
  Route::get('/get-noc-by-id/{id}','getNocById');
  Route::get('/get-maid-doc-expiry/{id}','getMaidDocExpiry');
  Route::post('/update-maid-doc-expiry','updateMaidDocExpiry');
});


}); // End User Middleware

Route::get('sign/cat1/{id}', [categoryOneClass::class, 'viewSignPageCat1'])->name('viewSignPageCat1');
Route::post('/save-signature-cat1',[categoryOneClass::class,'saveSignatureCat1'])->name('saveSignatureCat1');
Route::post('/save-signature-p4',[classCategory4Cntl::class ,'saveSignatureP4'])->name('saveSignatureP4');
Route::get('/sign/p4/{id}', [classCategory4Cntl::class, 'viewSignPageP4'])->name('viewSignPageP4');

        // Category one   
  Route::controller(categoryOneClass::class)->group(function(){
          Route::get('/get/invoice/cat1/{refCode}','viewCateoneInvoice')->name('viewCateoneInvoice');
          Route::get('/get/full/categoryone-contract/{contract}','viewFullContract')->name('viewFullContract');
          Route::get('get/contract/summary/{contract}','viewContractSummary')->name('viewContractSummary');
          Route::get('/fetch-per-connection/{service}', 'fetchPerConnection');
          Route::get('/get/full/categoryone-contract-arabic/{contract}','viewFullContractArabic')->name('viewFullContractArabic');
          Route::get('/transfer/letter-p1/{contract}', 'viewTransLetterP1')->name('viewTransLetterP1');
          Route::get('/agent/{contract}','viewAgentContract' );
      
        });
      
           // All route for Category 4
   Route::controller(classCategory4Cntl::class)->group(function(){
    Route::get('category4/contract/{id}', 'viewContract4Summary')->name('viewContract4Summary');
    Route::get('category4/contract-bycontract/{contract}', 'viewContractSummary4ByContractRef')->name('viewContractSummary4ByContractRef');
    Route::get('/get/invoice/cat4/{refCode}','viewCate4Invoice')->name('viewCate4Invoice');
    Route::get('/get/full-contract-cat4/{ref}','fullContractView')->name('fullContractView');
    Route::get('/get/arabic-p4/{ref}','fullContractArabicView')->name('fullContractArabicView');
    Route::get('/transfer-leter-p4/{ref}', 'p4_transfer_letter')->name('p4_transfer_letter');


  });


    
Route::controller(customerHcAndFc::class)->group(function(){
  Route::get('/all-p4/{name}','p4ContractsForAllBranchs');

});


Route::get('/sign-dd/{ref}', [DirectDebitController::class, 'showSignForm'])
     ->name('sign.dd');

Route::get('external/resign-dd/{ref}', [DirectDebitController::class, 'showResignForm'])
     ->name('resign.dd');

Route::post('external/resign-dd/{ref}', [DirectDebitController::class, 'updateResignSignature'])
     ->name('resign.dd.update'); 

Route::get('external/update-dd/{ref}', [DirectDebitController::class, 'showUpdateBankDetailsForm'])
     ->name('update.dd.bank');

Route::post('external/update-dd/{ref}', [DirectDebitController::class, 'updateBankDetails'])
     ->name('update.dd.bank.submit');

Route::get('external/resign-rejection/{ref}', [DirectDebitController::class, 'showResignRejectionForm'])
     ->name('resign.rejection');

Route::post('external/resign-rejection/{ref}', [DirectDebitController::class, 'updateResignRejectionSignature'])
     ->name('resign.rejection.update');


Route::post('/direct-debits/signature', [DirectDebitController::class, 'updateSignature']);


Route::prefix('survey')->group(function () {
    Route::get('/maid/{maid}', [SurveyMaidController::class, 'show'])->name('maid.survey.show');
    Route::post('/maid/{maid}', [SurveyMaidController::class, 'store'])->name('maid.survey.store');
    Route::get('/maid/{maid}/reviews', [SurveyMaidController::class, 'reviews'])
        ->name('maid.survey.reviews');
});

