<head>

    <!-- Required meta tags -->
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name="base-url" content="{{ url('') }}"/>

     <!-- BEGIN: Vendor CSS-->
     <link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/vendors/css/vendors.min.css">
     <link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/vendors/css/charts/apexcharts.css">
     <link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/vendors/css/extensions/toastr.min.css">
     <!-- END: Vendor CSS-->

    <!-- Bootstrap CSS -->


    <link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/css/bootstrap-extended.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/css/colors.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/css/components.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/css/themes/dark-layout.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/css/themes/bordered-layout.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/css/themes/semi-dark-layout.css">
  

     <!-- BEGIN: Page CSS-->
     <link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/css/core/menu/menu-types/vertical-menu.css">
     <link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/css/pages/dashboard-ecommerce.css">
     {{-- <link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/css/plugins/charts/chart-apex.css"> --}}
     <link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/css/plugins/extensions/ext-component-toastr.css">
     <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('/') }}assets/css/style.css">
    <!-- END: Custom CSS-->

    <title>@yield('title','Home')</title>
    <x-application-icon/>


<style>
    
.des_txt2 ul li {
    line-height: 2rem;
}    
    
</style>
</head>
