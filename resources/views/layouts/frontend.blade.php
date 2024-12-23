<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', ucwords(str_replace("_", " ", config('app.name', 'Laravel'))))</title>
    <link rel="icon" href="{{asset('/')}}assets/logo.png">

    <!-- Styles -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link href="{{ asset('/') }}assets/frontend/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('/') }}assets/frontend/css/fontawesome-all.min.css" rel="stylesheet">
    <link href="{{ asset('/') }}assets/frontend/css/swiper.css" rel="stylesheet">
    <link href="{{ asset('/') }}assets/frontend/css/styles.css" rel="stylesheet">
    @yield('css')
</head>
<body data-bs-spy="scroll" data-bs-target="#navbarExample">
    @include('layouts.include.frontend.nav')

    @yield('content')

    @include('layouts.include.frontend.footer')

    <!-- Scripts -->
    <script src="{{ asset('/') }}assets/frontend/js/bootstrap.min.js"></script> <!-- Bootstrap framework -->
    <script src="{{ asset('/') }}assets/frontend/js/swiper.min.js"></script> <!-- Swiper for image and text sliders -->
    <script src="{{ asset('/') }}assets/frontend/js/purecounter.min.js"></script> <!-- Purecounter counter for statistics numbers -->
    <script src="{{ asset('/') }}assets/frontend/js/replaceme.min.js"></script> <!-- ReplaceMe for rotating text -->
    <script src="{{ asset('/') }}assets/frontend/js/scripts.js"></script> <!-- Custom scripts -->
    @yield('script')
</body>
</html>