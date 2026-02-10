<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset="utf-8" />
    
     
        <title>{{ request()->path() }}</title>

           <link rel="icon" type="image/png" href="{{ env('logo') }}">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="" name="description" />
        <meta content="Coderthemes" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">
        <!-- App favicon -->
        <!-- <link rel="shortcut icon" href="{{ asset('backend/assets/images/favicon.ico') }}"> -->


        <link href="{{ asset('backend/assets/libs/mohithg-switchery/switchery.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets/libs/multiselect/css/multi-select.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets/libs/selectize/css/selectize.bootstrap3.css') }}" rel="stylesheet" type="text/css" />

      
        <!-- Include SweetAlert2 CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">


        <!-- <link href="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" type="text/css" /> -->

        <!-- Plugins css -->
        <link href="{{ asset('backend/assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css" />


        <!-- Bootstrap css -->
        <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- App css -->
        <link href="{{ asset('backend/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style"/>
        <!-- icons -->
        <link href="{{ asset('backend/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

        
        
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
        <!-- Head js -->
        <script src="{{ asset('backend/assets/js/head.js') }}"></script>



  <!-- dataTables -->
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css">

        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap5.min.css"/>
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css"/>

     

        <!-- dataTables end -->

         <link href="{{ asset('backend/assets/libs/spectrum-colorpicker2/spectrum.min.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ asset('backend/assets/libs/clockpicker/bootstrap-clockpicker.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet" type="text/css" />



        <style>
    body[data-leftbar-color="custom"]{
        background-color: #EFF3F6;
    }
    body[data-topbar-color="dark"] .navbar-custom {
        @if (auth()->user()->name == "Asmaa")
            background-color:rgb(246, 193, 211) !important;
        @else
        background-color:white !important;
            
        @endif
    }
</style>


<style>


/* Highlight when hovered or focused */
.form-select:hover,
.form-select:focus {
    background-color: white !important;  
    border-color: #FA8900 !important;  
    box-shadow: #FFEB00 !important;
    outline: none !important;
    cursor: pointer;
}

/* Also apply the same effect to Select2 elements */
.select2-selection:hover,
.select2-selection:focus {
    background-color: #ffeeba !important;  
    border-color: #ffc107 !important;
    box-shadow: 0px 0px 5px rgba(255, 193, 7, 0.28) !important;
    outline: none !important;
    cursor: pointer;
}


 </style>   



<!-- toastr -->

         <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" >
<!-- toastr -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" >


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />


    </head>

    <!-- body start -->
    <body data-layout-mode="default" data-theme="light" data-topbar-color="dark" data-menu-position="fixed" data-leftbar-color="dark" data-leftbar-size='default' data-sidebar-user='false'>





        <!-- Begin page -->
        <div id="wrapper">


            <!-- Topbar Start -->
           @include('body.header')
            <!-- end Topbar -->

            <!-- ========== Left Sidebar Start ========== -->
           @include('body.sidebar')
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">

               @yield('admin')

                <!-- Footer Start -->
                @include('body.footer')
                <!-- end Footer -->

            </div>

            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->


        </div>
        <!-- END wrapper -->

        <!-- Right Sidebar -->

        <!-- /Right-bar -->

        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>

        <!-- Vendor js -->
        <script src="{{ asset('backend/assets/js/vendor.min.js') }}"></script>


        <!-- for select 2 js -->
        <script src="{{ asset('backend/assets/libs/selectize/js/standalone/selectize.min.js') }}"></script>
        <script src="{{ asset('backend/assets/libs/select2/js/select2.min.js') }}"></script>
        <!-- <script src="{{ asset('backend/assets/libs/jquery-mockjax/jquery.mockjax.min.js') }}"></script> -->
        <script src="{{ asset('backend/assets/libs/devbridge-autocomplete/jquery.autocomplete.min.js') }}"></script>
        <!-- <script src="{{ asset('backend/assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script> -->
        <script src="{{ asset('backend/assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js') }}"></script>

        <!-- for select 2 js -->

        <!-- Plugins js-->
        <script src="{{ asset('backend/assets/libs/flatpickr/flatpickr.min.js') }}"></script>
        <!-- <script src="{{ asset('backend/assets/libs/apexcharts/apexcharts.min.js') }}"></script> -->

          <!-- Plugins js-->

          <!-- for date picker-->
        <script src="{{asset('backend/assets/libs/spectrum-colorpicker2/spectrum.min.js') }}"></script>
        <script src="{{asset('backend/assets/libs/clockpicker/bootstrap-clockpicker.min.js') }}"></script>
        <script src="{{asset('backend/assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

        <!-- for date picker End-->

        <script src="{{ asset('backend/assets/libs/mohithg-switchery/switchery.min.js') }}"></script>

        <!-- App js-->
        <script src="{{ asset('backend/assets/js/app.min.js') }}"></script>


      <!-- datatables js -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.3.0/js/responsive.bootstrap5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.bootstrap5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/keytable/2.7.0/js/dataTables.keyTable.min.js"></script>


  <!--end datatables js -->

        <script src="{{ asset('backend/assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
        <script src="{{ asset('backend/assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

        <!-- third party js ends -->


        <script src="{{ asset('backend/assets/js/pages/datatables.init.js') }}"></script>
         <!-- Datatables Eend -->


        <!-- Include SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- moment for time format-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>


         <script src="{{ asset('backend/assets/js/code.js') }}"></script>

          <script src="{{ asset('backend/assets/js/validate.min.js') }}"></script>



        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

        <script>
         @if(Session::has('message'))
         var type = "{{ Session::get('alert-type','info') }}"
         switch(type){
            case 'info':
            toastr.info(" {{ Session::get('message') }} ");
            break;

            case 'success':
            toastr.success(" {{ Session::get('message') }} ");
            break;

            case 'warning':
            toastr.warning(" {{ Session::get('message') }} ");
            break;

            case 'error':
            toastr.error(" {{ Session::get('message') }} ");
            break;
         }
         @endif
        </script>


        <!-- Dashboar 1 init js-->
        <!-- <script src="{{ asset('backend/assets/js/pages/dashboard-1.init.js') }}"></script> -->

        <!-- Init js-->
        <script src="{{ asset('backend/assets/js/pages/form-advanced.init.js') }}"></script>

         <!-- Init js-->
         <script src="{{ asset('backend/assets/js/pages/form-pickers.init.js') }}"></script>

        @yield('scriptForCategory4contracts')
        @stack('scripts')  <!-- Placeholder for page-specific scripts -->
    </body>
</html>
