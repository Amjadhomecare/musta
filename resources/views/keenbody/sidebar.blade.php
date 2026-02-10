{{-- resources/views/layouts/partials/sidebar.blade.php --}}

@php
    /*
    |--------------------------------------------------------------------------
    | Sidebar helpers
    |--------------------------------------------------------------------------
    |  – menuItemOpen() → returns "here show"  (keeps accordion open)
    |  – linkActive()   → returns "active"     (highlights current link)
    */
    if (! function_exists('menuItemOpen')) {
        function menuItemOpen(array $patterns): string
        {
            foreach ($patterns as $p) {
                if (request()->is($p) || request()->routeIs($p)) {
                    return 'here show';
                }
            }
            return '';
        }
    }

    if (! function_exists('linkActive')) {
        function linkActive(array $patterns): string
        {
            foreach ($patterns as $p) {
                if (request()->is($p) || request()->routeIs($p)) {
                    return 'active';
                }
            }
            return '';
        }
    }
@endphp

<div id="kt_app_sidebar"
     class="app-sidebar flex-column"
     data-kt-drawer="true"
     data-kt-drawer-name="app-sidebar"
     data-kt-drawer-activate="{default:true,lg:false}"
     data-kt-drawer-overlay="true"
     data-kt-drawer-width="225px"
     data-kt-drawer-direction="start"
     data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">

    {{-- LOGO --}}
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <a href="/all/maids">
            <img alt="Logo" src="{{ env('logo') }}" class="h-30px app-sidebar-logo-default"/>
        </a>
        <div id="kt_app_sidebar_toggle"
             class="app-sidebar-toggle btn btn-icon btn-sm h-30px w-30px rotate"
             data-kt-toggle="true"
             data-kt-toggle-state="active"
             data-kt-toggle-target="body"
             data-kt-toggle-name="app-sidebar-minimize">
            <i class="ki-duotone ki-double-left fs-2 rotate-180">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </div>
    </div>

    {{-- MENU --}}
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <div id="kt_app_sidebar_menu_scroll"
                 class="hover-scroll-y my-5 mx-3"
                 data-kt-scroll="true"
                 data-kt-scroll-activate="true"
                 data-kt-scroll-height="auto"
                 data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                 data-kt-scroll-wrappers="#kt_app_sidebar_menu"
                 data-kt-scroll-offset="5px"
                 data-kt-scroll-save-state="true">

                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold"
                     id="kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false">

              

                    {{-- ========= DASHBOARD HEADING ========= --}}

                    {{-- ========= DASHBOARDS ========= --}}
                    <div data-kt-menu-trigger="click"
                         class="menu-item menu-accordion {{ menuItemOpen([
                             'typing/*','list/non/contract*','ar-ads*','some/other/route*'
                         ]) }}">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="fas fa-chart-pie"></i></span>
                            <span class="menu-title">Quick link</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a class="menu-link {{ linkActive(['typing/all/invoices*']) }}"
                                   href="/typing/all/invoices">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Typing</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link {{ linkActive(['list/non/contract*']) }}"
                                   href="/list/non/contract">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title"> On Fly Invoice</span>
                                </a>
                            </div>

                                 <div class="menu-item">
                                <a class="menu-link {{ linkActive(['ar-ads*']) }}"
                                   href="/ar-ads">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Advance list</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- ========= PAGES HEADING ========= --}}
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Pages</span>
                        </div>
                    </div>
                        <div data-kt-menu-trigger="click"
                             class="menu-item menu-accordion {{ menuItemOpen([
                                 'upload-document*','page-report*','view_income_statment*',
                                 'dynamic-report*','log-book*','table-wrost-p4*',
                                 'vue/online-report*',
                                  'vue/jv-log*',

                             ]) }}">
                            <span class="menu-link">
                                <span class="menu-icon"> <i class="ki-outline ki-graph-up text-primary fs-2x"></i> </span>
                                <span class="menu-title">Reports</span>
                                <span class="menu-arrow"></span>
                            </span>
                          @if(auth()->user()->group === 'accounting' || auth()->user()->group === 'owner' )
                            <div class="menu-sub menu-sub-accordion">
                                <div class="menu-item"><a class="menu-link {{ linkActive(['upload-document*']) }}" href="/upload-document">Document Upload</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['vue/amir*']) }}" href="/vue/amir">Dashboard General Report</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['view_income_statment*']) }}" href="/view_income_statment">Income Statement</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['log-book*']) }}" href="/log-book">Maid Editing Log</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['vue/jv-log*']) }}" href="/vue/jv-log">Journal amount Editing Log</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['table-wrost-p4*']) }}" href="/table-wrost-p4">Worst Maids P4</a></div>
                            </div>
                        @endif
                        </div>
               

       
<!--                  
                   {{-- ========= NETLINK ========= --}}
                    <div data-kt-menu-trigger="click"
                         class="menu-item menu-accordion {{ menuItemOpen(['vue/net-link*']) }}">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="fa-solid fa-money-check-alt fs-2 text-white"></i></span>
                            <span class="menu-title">NetWork</span>
                        
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a class="menu-link {{ linkActive(['vue/net-link*']) }}" href="/vue/net-link">
                                   Network list
                                </a>
                            </div>
                        </div>
                    </div>

                                 -->

          
                    {{-- ========= APPS HEADING ========= --}}
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Apps</span>
                        </div>
                    </div>

                    {{-- ========= ADMIN (Accounting only) ========= --}}
                    @if(auth()->user()->group === 'accounting')
                        <div data-kt-menu-trigger="click"
                             class="menu-item menu-accordion {{ menuItemOpen(['view_add_user*']) }}">
                            <span class="menu-link">
                                <span class="menu-icon"><i class="bi bi-file-person fs-2 text-white"></i></span>
                                <span class="menu-title">Admin</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <div class="menu-sub menu-sub-accordion">
                                <div class="menu-item"><a class="menu-link {{ linkActive(['view_add_user*']) }}" href="{{ route('view_add_user') }}">Add User</a></div>
                            </div>
                        </div>
                    @endif

                    {{-- ========= FINANCE ========= --}}
                    <div data-kt-menu-trigger="click"
                         class="menu-item menu-accordion {{ menuItemOpen([
                             'pageCashir*','viewAllRegistredGeneralJVCntl*','viewPreConnectionGeneralJVCntl*',
                             'viewInvoicesPreConnectionsCntl*','viewSearchStatmentAccountCntl*',
                             'viewRegisterNewLedgerCntl*','viewTrialBalanceCntl*','viewPagePandL*',
                             'balance-sheet*','vue/customer-balance*','pendingArrivalList',
                             'pendingReleaseList','bulk.jv','upload-document' ,'vue/recursions-jv',
                             'vue/refund-dd*', 'report-recipients'
                         ]) }}">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="fa-brands fa-d-and-d fs-2 text-white"></i></span>
                            <span class="menu-title">Finance</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            @if(auth()->user()->group === 'accounting')
                                <div class="menu-item"><a class="menu-link {{ linkActive(['pageCashir*']) }}" href="{{ route('pageCashir') }}">Cashier</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['viewAllRegistredGeneralJVCntl*']) }}" href="{{ route('viewAllRegistredGeneralJVCntl') }}">All General JV</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['viewPreConnectionGeneralJVCntl*']) }}" href="{{ route('viewPreConnectionGeneralJVCntl') }}">Accounting Pre-connection</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['viewInvoicesPreConnectionsCntl*']) }}" href="{{ route('viewInvoicesPreConnectionsCntl') }}">Invoices Pre-connection</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['vue/recursions-jv*']) }}" href="/vue/recursions-jv">Recurring Journal Vouchers</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['bulk.jv']) }}" href="{{ route('bulk.jv') }}">Bulk JV with DR</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['viewSearchStatmentAccountCntl*']) }}" href="{{ route('viewSearchStatmentAccountCntl') }}">Statement of Account</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['viewRegisterNewLedgerCntl*']) }}" href="{{ route('viewRegisterNewLedgerCntl') }}">Add New Ledger</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['viewTrialBalanceCntl*']) }}" href="{{ route('viewTrialBalanceCntl') }}">Trial Balance</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['viewPagePandL*']) }}" href="{{ route('viewPagePandL') }}">Income Statement</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['balance-sheet*']) }}" href="{{ route('balance-sheet') }}">Balance Sheet</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['vue/customer-balance*']) }}" href="/vue/customer-balance">A/R Balance</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['erp.comparative-trial']) }}" href="{{ route('erp.comparative-trial') }}">Comparative Trial</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['pendingArrivalList']) }}" href="{{ route('pendingArrivalList') }}">Approving Arrival <strong class="badge bg-danger  ms-2">{{ App\Models\Arrival::getPendingCount() }}</strong></a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['pendingReleaseList']) }}" href="{{ route('pendingReleaseList') }}">Approving Released <strong class="badge bg-danger ms-2">{{ App\Models\release::getPendingCount() }}</strong></a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['upload-document']) }}" href="/upload-document">Document Upload</a></div>
                                <div class="menu-item"><a class="menu-link {{ linkActive(['report-recipients']) }}" href="/report-recipients">Resgister report recipients </a></div>
                            @endif
                        </div>
                    </div>

                    {{-- ========= PACKAGE FOUR MANAGER ========= --}}
                    <div data-kt-menu-trigger="click"
                         class="menu-item menu-accordion {{ menuItemOpen([
                             'viewAllInvoicesCat4*','viewAddingCategory4Contract*','pageInstallment*',
                             'viewAllCat4Cntl*','payroll.index','page.audi.dh*',
                             'viewFormAdvanceAndDeductionCntl*','viwePaidMaids*','p4-audit*',
                             'vue/sms-logs*','sign/pp-p4*'
                         ]) }}">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="fa-solid fa-dice-four fs-2 text-white"></i></span>
                            <span class="menu-title">Package 4 Manager</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item"><a class="menu-link {{ linkActive(['viewAllInvoicesCat4*']) }}" href="{{ route('viewAllInvoicesCat4') }}">All P4 Invoices</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['viewAddingCategory4Contract*']) }}" href="{{ route('viewAddingCategory4Contract') }}">Add New Contract</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['pageInstallment*']) }}" href="{{ route('pageInstallment') }}">Upcoming Installment</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['viewAllCat4Cntl*']) }}" href="{{ route('viewAllCat4Cntl') }}">All P4 Contracts</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['payroll.index']) }}" href="{{ route('payroll.index') }}">P4 Maids Payroll</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['page.audi.dh*']) }}" href="{{ route('page.audi.dh') }}">P5 Audit Direct Hire</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['viewFormAdvanceAndDeductionCntl*']) }}" href="{{ route('viewFormAdvanceAndDeductionCntl') }}">Add Advance/Allowance</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['viwePaidMaids*']) }}" href="{{ route('viwePaidMaids') }}">Payroll Maids History</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['p4-audit*']) }}" href="/p4-audit">Audit</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['vue/sms-logs*']) }}" href="/vue/sms-logs">Log SMS</a></div>

                        </div>
                    </div>

                    {{-- ========= PACKAGE ONE MANAGER ========= --}}
                    <div data-kt-menu-trigger="click"
                         class="menu-item menu-accordion {{ menuItemOpen([
                             'viewAddingCategory1Contract*','viewAllCat1Cntl*',
                             'categoryOneInvoicesList*','vue/sign-pp*'
                         ]) }}">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="fa-solid fa-dice-one fs-2 text-white"></i></span>
                            <span class="menu-title">Package 1 Manager</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item"><a class="menu-link {{ linkActive(['viewAddingCategory1Contract*']) }}" href="{{ route('viewAddingCategory1Contract') }}">Add New Contract</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['viewAllCat1Cntl*']) }}" href="{{ route('viewAllCat1Cntl') }}">All P1 Contracts</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['categoryOneInvoicesList*']) }}" href="{{ route('categoryOneInvoicesList') }}">All P1 Invoices</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['vue/sign-pp*']) }}" href="/vue/sign-pp">Submit Signature</a></div>
                        </div>
                    </div>

                    {{-- ========= MAIDS CONTROL PANEL ========= --}}
                    <div data-kt-menu-trigger="click"
                         class="menu-item menu-accordion {{ menuItemOpen([
                             'viewMaidsCVCntl*','pageMaidAttachment*','maidinterview.report'
                         ]) }}">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="bi bi-file-person fs-2 text-white"></i></span>
                            <span class="menu-title">Maids Control Panel</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item"><a  class="menu-link {{ linkActive(['viewMaidsCVCntl*']) }}" href="{{ route('viewMaidsCVCntl') }}">All CVs</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['pageMaidAttachment*']) }}" href="{{ route('pageMaidAttachment') }}">Maid Attachment</a></div>
                            <!-- <div class="menu-item"><a class="menu-link {{ linkActive(['maidinterview.report']) }}" href="{{ route('maidinterview.report') }}">Interview Report</a></div> -->
                        </div>
                    </div>

                    {{-- ========= CUSTOMERS ========= --}}
                    <div data-kt-menu-trigger="click"
                         class="menu-item menu-accordion {{ menuItemOpen([
                             'listOfcustomers*','pageCustomerAttachment*','vue/sms*',
                             'vue/sign-pp*','vue/sign-list*','vue/wired-transfer*'
                         ]) }}">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="fa-solid fa-person-half-dress fs-2 text-white"></i></span>
                            <span class="menu-title">Customers</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item"><a class="menu-link {{ linkActive(['listOfcustomers*']) }}" href="{{ route('listOfcustomers') }}">All Customers</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['pageCustomerAttachment*']) }}" href="{{ route('pageCustomerAttachment') }}">Customers Attachment</a></div>
                            <!-- <div class="menu-item"><a class="menu-link {{ linkActive(['vue/sms*']) }}" href="/vue/sms">Send Message</a></div> -->
                            <div class="menu-item"><a class="menu-link {{ linkActive(['vue/sign-pp*']) }}" href="/vue/sign-pp">Submit Signature</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['vue/sign-list*']) }}" href="/vue/sign-list">Signature Report</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['vue/wired-transfer*']) }}" href="/vue/wired-transfer">Wire Payment</a></div>
                        </div>
                    </div>

                    {{-- ========= COMPLAIN DEPARTMENT ========= --}}
                    <div data-kt-menu-trigger="click"
                         class="menu-item menu-accordion {{ menuItemOpen([
                             'formCreditMemo*','arrivalList*','listReturnCat4*','listReturnCat1*',
                             'pageReleaseCv*','vue/salary-maid-p1*'
                         ]) }}">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="fa-solid fa-people-robbery fs-2 text-white"></i></span>
                            <span class="menu-title">Complain Department</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item"><a class="menu-link {{ linkActive(['formCreditMemo*']) }}" href="{{ route('formCreditMemo') }}">Credit Memo</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['arrivalList*']) }}" href="{{ route('arrivalList') }}">Arrival</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['listReturnCat4*']) }}" href="{{ route('listReturnCat4') }}">Return List Cat 4</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['listReturnCat1*']) }}" href="{{ route('listReturnCat1') }}">Return List Cat 1</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['pageReleaseCv*']) }}" href="{{ route('pageReleaseCv') }}">Maids Released & Runaway</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['vue/salary-maid-p1*']) }}" href="/vue/salary-maid-p1">Salary P1 List</a></div>
                        </div>
                    </div>

                    {{-- ========= BULK ========= --}}
                    <div data-kt-menu-trigger="click"
                         class="menu-item menu-accordion {{ menuItemOpen(['blk-*']) }}">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="fa-solid fa-hat-wizard fs-2 text-white"></i></span>
                            <span class="menu-title">Bulk</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item"><a class="menu-link {{ linkActive(['blk-maid']) }}" target="_blank" href="{{ route('blk-maid') }}">Maid Bulk</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['blk-customer']) }}" target="_blank" href="{{ route('blk-customer') }}">Customer Bulk</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['blk-p4']) }}" target="_blank" href="{{ route('blk-p4') }}">Contract P4 Bulk</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['blk-upcoming']) }}" target="_blank" href="{{ route('blk-upcoming') }}">Upcoming Bulk</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['blk-p1']) }}" target="_blank" href="{{ route('blk-p1') }}">Contract P1 Bulk</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['blk-payroll']) }}" href="{{ route('blk-payroll') }}">Bulk Payroll</a></div>
                        </div>
                    </div>

                    {{-- ========= HR ========= --}}
                    <div data-kt-menu-trigger="click"
                         class="menu-item menu-accordion {{ menuItemOpen([
                             'pageMaidAttachment*','table-p4*','intreview.index','vue/leave-salary*',
                             'vue/leave-salary-staff*','payroll.index','page.audi.dh*',
                             'viewFormAdvanceAndDeductionCntl*','viwePaidMaids*',
                             'vue/maids-visit-visa*','vue/ticket-maid*','vue/salary-maid-p1*',
                             'vue/noc*'
                         ]) }}">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="fa-solid fa-cube fs-2 text-white"></i></span>
                            <span class="menu-title">HR</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item"><a class="menu-link {{ linkActive(['pageMaidAttachment*']) }}" href="{{ route('pageMaidAttachment') }}">Maid Attachment</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['table-p4*']) }}" href="/table-p4">Maids P4 Doc Expired</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['vue/leave-salary']) }}" href="/vue/leave-salary">Maid Leave Salary</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['vue/leave-salary-staff*']) }}" href="/vue/leave-salary-staff">Staff Leave Salary</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['viewFormAdvanceAndDeductionCntl*']) }}" href="{{ route('viewFormAdvanceAndDeductionCntl') }}">Add Advance/Allowance</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['viwePaidMaids*']) }}" href="{{ route('viwePaidMaids') }}">Payroll Maids History</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['vue/maids-visit-visa*']) }}" href="/vue/maids-visit-visa?remove_null=1">Visit Visa Expiration</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['vue/ticket-maid*']) }}" href="/vue/ticket-maid">Ticket Maids</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['vue/salary-maid-p1*']) }}" href="/vue/salary-maid-p1">Salary P1 List</a></div>
                  
                        </div>
                    </div>

                        {{-- ========= PRO ========= --}}
                        <div data-kt-menu-trigger="click"
                            class="menu-item menu-accordion {{ (request()->is('vue/pay-order*') || request()->is('vue/apply-visa*')) ? 'here show' : '' }}">
                            <span class="menu-link">
                                <span class="menu-icon"><i class="fa-solid fa-briefcase fs-2 text-white"></i></span>
                                <span class="menu-title">PRO</span>
                                <span class="menu-arrow"></span>
                            </span>

                            <div class="menu-sub menu-sub-accordion">
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->is('vue/pay-order*') ? 'active' : '' }}"
                                    href="{{ url('/vue/pay-order') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">Record Payment</span>
                                    </a>

                                    <a class="menu-link {{ request()->is('vue/apply-visa*') ? 'active' : '' }}"
                                    href="{{ url('/vue/apply-visa') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">Apply visa</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                    {{-- ========= TO EXCEL ========= --}}
                    <div data-kt-menu-trigger="click"
                         class="menu-item menu-accordion {{ menuItemOpen([
                             'maid-cost*','p1-report-balance*','p4-report-balance*','no-filter*'
                         ]) }}">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="fa-solid fa-robot fs-2 text-white"></i></span>
                            <span class="menu-title">To Excel</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item"><a class="menu-link {{ linkActive(['p1-report-balance*']) }}" href="/p1-report-balance">All P1 with Balance</a></div>
                            <div class="menu-item"><a class="menu-link {{ linkActive(['p4-report-balance*']) }}" href="/p4-report-balance">All P4 with Balance</a></div>
                        </div>
                    </div>

                    {{-- ========= END MENU ROOT ========= --}}
                </div>
            </div>
        </div>
    </div>
</div>



<script>
    document.addEventListener("keydown", function(event) {

        if (event.key === "1" && !event.target.matches("input, textarea, select")) {
            event.preventDefault(); 
            window.open("/all/maids", "_blank");
        }

        if (event.key === "2" && !event.target.matches("input, textarea, select")) {
            event.preventDefault(); 
           window.open("/arrival", "_blank");
        }

        if (event.key === "3" && !event.target.matches("input, textarea, select")) {
        event.preventDefault(); 
        window.open("/all-customers", "_blank");
       }

        if (event.key === "7" && !event.target.matches("input, textarea, select")) {
            event.preventDefault(); 
            window.open("/all/general/jv","_blank")
        }

        
        if (event.key === "4" && !event.target.matches("input, textarea, select")) {
            event.preventDefault(); 
            window.open("/search/ledger","_blank")
        }

        if (event.key === "5" && !event.target.matches("input, textarea, select")) {
            event.preventDefault(); 
            window.open("/return-list-cat1","_blank")
        }

        if (event.key === "6" && !event.target.matches("input, textarea, select")) {
            event.preventDefault(); 
            window.open("/approving-maid","_blank")
        }


        if (event.key === "+" && !event.target.matches("input, textarea, select")) {
            event.preventDefault(); 
            window.open("/intreview","_blank")
        }
        


        if (event.key === "t" && !event.target.matches("input, textarea, select")) {
            event.preventDefault(); 
            window.location.href = "/typing/all/invoices";
        }
    });
</script>
