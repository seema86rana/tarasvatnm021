<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ ucwords(str_replace("_", " ", config('app.name', 'Laravel'))) }}</title>
    <link rel="icon" href="{{asset('/')}}assets/logo.png">
    
    <!-- Global stylesheets -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/backend/css/icons/fontawesome/styles.min.css')}}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/backend/css/icons/icomoon/styles.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/backend/css/bootstrap.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/backend/css/core.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/backend/css/components.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/backend/css/colors.css') }}" rel="stylesheet" type="text/css">

    <link rel="stylesheet" href="{{ asset('assets/backend/css/sweetalert/sweetalert2.min.css') }}">

    <link href="{{ asset('assets/backend/css/custom.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/backend/css/style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/backend/css/admin_common.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/backend/css/loader.css')}}" rel="stylesheet" type="text/css">
    @if(Route::is('admin.home_carousels'))
        <link href="{{ asset('assets/backend/css/rowReorder.dataTables.min.css') }}" rel="stylesheet" type="text/css">
    @endif
    @section('header_css')
    @show
</head>

<body class="navbar-top">
    <div class="load-main hidden" >
        <div class="loader-block">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
    @include('layouts.include.backend.nav')

    <div class="page-container">
        <div class="page-content">
            @include('layouts.include.backend.sidebar')
            <div class="content-wrapper">
                @section('page_header')
                @show
                <div class="content">
                    @section('content')
                    @show
                    <div class="footer text-muted">
                        &copy; {{ date('Y') }}. <a href="javascript: void(0)">{{ ucwords(str_replace("_", " ", config('app.name', 'Laravel'))) }}</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="overlay hide" id="loader">
        <span class="spinner-glow center-custom spinner-glow-info mr-5"></span>
    </div>
    <script type="text/javascript">
         var BASE_URL='<?php echo url("/"); ?>';
         var ADMIN_URL='<?php echo url("/")."/admin"; ?>';
    </script>
    <!-- Core JS files -->
    <script type="text/javascript" src="{{ asset('assets/backend/js/plugins/loaders/pace.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/backend/js/core/libraries/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/backend/js/core/libraries/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/backend/js/plugins/loaders/blockui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/backend/js/plugins/ui/moment/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/backend/js/core/app.js') }}"></script>

    <script src="{{ asset('assets/backend/js/sweetalert/sweetalert2.min.js') }}"></script>

    <script type="text/javascript" src="{{ asset('assets/backend/js/plugins/forms/styling/uniform.min.js') }}"></script>

	<script type="text/javascript" src="{{ asset('assets/backend/js/plugins/forms/validation/validate.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('assets/backend/js/plugins/forms/validation/additional_methods.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/backend/js/plugins/forms/styling/uniform.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/backend/js/plugins/notifications/jgrowl.min.js') }}"></script>

	@section('footer_js')
	@show

    <script type="text/javascript" src="{{ asset('assets/backend/js/custom.js') }}"></script>
</body>

</html>