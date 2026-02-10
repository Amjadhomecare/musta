
import { handleAjaxReportDisplay ,displayDynamicReport } from "../reuseable/ajaxSubmit";


const html = `<div class="content">
                            <!-- Start Content-->
                            <div class="container-fluid">
                                
                                <div class="row">
                                    <div class="col-md-6 col-xl-3">
                                        <div class="widget-rounded-circle card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="avatar-lg rounded-circle bg-primary border-primary border shadow">
                                                            <i class="fe-heart font-22 avatar-title text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="text-end">
                                                            <h3 class="text-dark mt-1">AED<span data-plugin="counterup">58</span></h3>
                                                            <p class="text-muted mb-1 text-truncate">Total Revenue</p>
                                                        </div>
                                                    </div>
                                                </div> <!-- end row-->
                                            </div>
                                        </div> <!-- end widget-rounded-circle-->
                                    </div> <!-- end col-->

                                    <div class="col-md-6 col-xl-3">
                                        <div class="widget-rounded-circle card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="avatar-lg rounded-circle bg-success border-success border shadow">
                                                            <i class="fe-shopping-cart font-22 avatar-title text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="text-end">
                                                            <h3 class="text-dark mt-1"><span data-plugin="counterup">127</span></h3>
                                                            <p class="text-muted mb-1 text-truncate">Today's Sales</p>
                                                        </div>
                                                    </div>
                                                </div> <!-- end row-->
                                            </div>
                                        </div> <!-- end widget-rounded-circle-->
                                    </div> <!-- end col-->

                                    <div class="col-md-6 col-xl-3">
                                        <div class="widget-rounded-circle card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="avatar-lg rounded-circle bg-info border-info border shadow">
                                                            <i class="fe-bar-chart-line- font-22 avatar-title text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="text-end">
                                                            <h3 class="text-dark mt-1"><span data-plugin="counterup">0.58</span>%</h3>
                                                            <p class="text-muted mb-1 text-truncate">Conversion</p>
                                                        </div>
                                                    </div>
                                                </div> <!-- end row-->
                                            </div>
                                        </div> <!-- end widget-rounded-circle-->
                                    </div> <!-- end col-->

                                    <div class="col-md-6 col-xl-3">
                                        <div class="widget-rounded-circle card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="avatar-lg rounded-circle bg-warning border-warning border shadow">
                                                            <i class="fe-eye font-22 avatar-title text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="text-end">
                                                            <h3 class="text-dark mt-1"><span data-plugin="counterup">78.41</span>k</h3>
                                                            <p class="text-muted mb-1 text-truncate">Today's Visits</p>
                                                        </div>
                                                    </div>
                                                </div> <!-- end row-->
                                            </div>
                                        </div> <!-- end widget-rounded-circle-->
                                    </div> <!-- end col-->
                                </div>
                                <!-- end row-->

                            

                                <div class="row">
                                    <div class="col-xl-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="dropdown float-end">
                                                    <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="mdi mdi-dots-vertical"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <!-- item-->
                                                        <a href="javascript:void(0);" class="dropdown-item">Edit Report</a>
                                                        <!-- item-->
                                                        <a href="javascript:void(0);" class="dropdown-item">Export Report</a>
                                                        <!-- item-->
                                                        <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                                    </div>
                                                </div>

                                                <h4 class="header-title mb-3">Top 5 Users Balances</h4>

                                                <div class="table-responsive">
                                                    <table class="table table-borderless table-hover table-nowrap table-centered m-0">

                                                        <thead class="table-light">
                                                            <tr>
                                                                <th colspan="2">Profile</th>
                                                                <th>Currency</th>
                                                                <th>Balance</th>
                                                                <th>Reserved in orders</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td style="width: 36px;">
                                                                    <img src="assets/images/users/user-2.jpg" alt="contact-img" title="contact-img" class="rounded-circle avatar-sm" />
                                                                </td>

                                                                <td>
                                                                    <h5 class="m-0 fw-normal">Tomaslau</h5>
                                                                    <p class="mb-0 text-muted"><small>Member Since 2017</small></p>
                                                                </td>

                                                                <td>
                                                                    <i class="mdi mdi-currency-btc text-primary"></i> BTC
                                                                </td>

                                                                <td>
                                                                    0.00816117 BTC
                                                                </td>

                                                                <td>
                                                                    0.00097036 BTC
                                                                </td>

                                                                <td>
                                                                    <a href="javascript: void(0);" class="btn btn-xs btn-light"><i class="mdi mdi-plus"></i></a>
                                                                    <a href="javascript: void(0);" class="btn btn-xs btn-danger"><i class="mdi mdi-minus"></i></a>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div> <!-- end col -->

                                    <div class="col-xl-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="dropdown float-end">
                                                    <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="mdi mdi-dots-vertical"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <!-- item-->
                                                        <a href="javascript:void(0);" class="dropdown-item">Edit Report</a>
                                                        <!-- item-->
                                                        <a href="javascript:void(0);" class="dropdown-item">Export Report</a>
                                                        <!-- item-->
                                                        <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                                    </div>
                                                </div>

                                                <h4 class="header-title mb-3">Revenue History</h4>

                                                <div class="table-responsive">
                                                    <table class="table table-borderless table-nowrap table-hover table-centered m-0">

                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Marketplaces</th>
                                                                <th>Date</th>
                                                                <th>Payouts</th>
                                                                <th>Status</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>
                                                                    <h5 class="m-0 fw-normal">Themes Market</h5>
                                                                </td>

                                                                <td>
                                                                    Oct 15, 2018
                                                                </td>

                                                                <td>
                                                                    AED5848.68
                                                                </td>

                                                                <td>
                                                                    <span class="badge bg-soft-warning text-warning">Upcoming</span>
                                                                </td>

                                                                <td>
                                                                    <a href="javascript: void(0);" class="btn btn-xs btn-light"><i class="mdi mdi-pencil"></i></a>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div> <!-- end .table-responsive-->
                                            </div>
                                        </div> <!-- end card-->
                                    </div> <!-- end col -->
                                </div>
                                <!-- end row -->
                                
                            </div> <!-- container -->

                        </div> <!-- content --> 
                `
document.addEventListener('DOMContentLoaded', () => {
                    handleAjaxReportDisplay('reportForm', "/fetch", html, "POST", 'reportResult');
                    displayDynamicReport('/fetch/cash/equivalent', 'GET', displayCashReport);
                    displayDynamicReport('/fetch/past/three/typing', 'GET', displayReportForThreeMonthTyping);
                    displayDynamicReport('/fetch/past/three/p1', 'GET', displayReportForThreeMonthP1);
                    displayDynamicReport('/fetch/past/three/p4', 'GET', displayReportForThreeMonthP4);
        });
                
 function displayReportForThreeMonthTyping(data) {
    const reportContainer = document.getElementById('lastThreeMonths');
    reportContainer.innerHTML = ''; 

    data.forEach(item => {
        const card = document.createElement('div');
        card.classList.add('col-md-6', 'col-xl-3');

        card.innerHTML = `
            <div class="card" id="tooltip-container">
             <div class="card-body">
              <div class="avatar-sm bg-warning rounded shadow-lg">
                                                    <i class="fe-bar-chart-2 avatar-title font-22 text-white"></i>
                                                </div>
               

                        <a>Typing ${item.month}</a>
                    </h4>
                    <h2 class="text-primary my-3 text-center">AED<span data-plugin="counterup">${item.amount}</span></h2>
                </div>
            </div>
        `;

        reportContainer.appendChild(card);
    });
}

function displayReportForThreeMonthP1(data) {
    const reportContainer = document.getElementById('lastThreeMonthsPackage1');
    reportContainer.innerHTML = ''; 

    data.forEach(item => {
        const card = document.createElement('div');
        card.classList.add('col-md-6', 'col-xl-3');

        card.innerHTML = `
            <div class="card" id="tooltip-container">
             <div class="card-body">
              <div class="avatar-sm bg-warning rounded shadow-lg">
                                                    <i class="fe-bar-chart-line- avatar-title font-22 text-white"></i>
                                                </div>
               

                        <a>Package one ${item.month}</a>
                    </h4>
                    <h2 class="text-primary my-3 text-center">AED<span data-plugin="counterup">${item.amount}</span></h2>
                </div>
            </div>
        `;

        reportContainer.appendChild(card);
    });
}



function displayReportForThreeMonthP4(data) {
    const reportContainer = document.getElementById('lastThreeMonthsPackage4');
    reportContainer.innerHTML = ''; 

    data.forEach(item => {
        const card = document.createElement('div');
        card.classList.add('col-md-6', 'col-xl-3');

        card.innerHTML = `
            <div class="card" id="tooltip-container">
             <div class="card-body">
              <div class="avatar-sm bg-warning rounded shadow-lg">
                                                    <i class="fe-bar-chart-line- avatar-title font-22 text-white"></i>
                                                </div>
               

                        <a>Package one ${item.month}</a>
                    </h4>
                    <h2 class="text-primary my-3 text-center">AED<span data-plugin="counterup">${item.amount}</span></h2>
                </div>
            </div>
        `;

        reportContainer.appendChild(card);
    });
}

                
function displayCashReport(data) {
        const reportContainer = document.getElementById('reportCash');
        reportContainer.innerHTML = ''; 
    
        data.forEach(item => {
            const card = document.createElement('div');
            card.classList.add('col-md-6', 'col-xl-3');
    
            card.innerHTML = `
                <div class="card" id="tooltip-container">
                    <div class="card-body">
                        <i class="fa fa-info-circle text-muted float-end" data-bs-container="#tooltip-container" data-bs-toggle="tooltip" data-bs-placement="bottom" title="More Info"></i>
                        <h4 class="mt-0 font-16">
                            <a target="_blank" href="/search/ledger?date_range=2020-01-01+to+2027-01-31&selected_ledger=${item.item}">${item.item}</a>
                        </h4>
                        <h2 class="text-primary my-3 text-center">AED<span data-plugin="counterup">${item.balance}</span></h2>
                    </div>
                </div>
            `;
    
            reportContainer.appendChild(card);
        });
    }
    