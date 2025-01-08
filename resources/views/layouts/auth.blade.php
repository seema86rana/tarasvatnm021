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
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,400;0,600;1,400&display=swap"
        rel="stylesheet">
    <link href="{{ asset('/') }}assets/frontend/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('/') }}assets/frontend/css/fontawesome-all.min.css" rel="stylesheet">
    <link href="{{ asset('/') }}assets/frontend/css/swiper.css" rel="stylesheet">
    <link href="{{ asset('/') }}assets/frontend/css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    @yield('css')
</head>

<body data-bs-spy="scroll" data-bs-target="#navbarExample">
    <nav id="navbarExample" class="navbar navbar-expand-lg fixed-top navbar-light" aria-label="Main navigation">
        <div class="container">

            <!-- Image Logo -->
            <a class="navbar-brand logo-image" href="/"><img src="{{ asset('/') }}assets/logo.svg" alt="alternative"></a>

            <!-- Text Logo - Use this if you don't have a graphic logo -->
            <!-- <a class="navbar-brand logo-text" href="/">Ioniq</a> -->

            <button class="navbar-toggler p-0 border-0" type="button" id="navbarSideCollapse"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto navbar-nav-scroll">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{ route('home') }}#header">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}#details">Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}#faq">FAQ</a>
                    </li>
                </ul>
                <span class="nav-item">
                @auth
                    <a class="btn-outline-sm" href="javascript:void(0);"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log out</a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                @else
                    <a class="btn-outline-sm" href="{{ route('login') }}">Log in</a>
                @endauth
            </span>
            </div> <!-- end of navbar-collapse -->
        </div> <!-- end of container -->
    </nav>

    @yield('content')

    @include('layouts.include.frontend.footer')

    <!-- Scripts -->
    <script src="{{ asset('/') }}assets/frontend/js/bootstrap.min.js"></script> <!-- Bootstrap framework -->
    <script src="{{ asset('/') }}assets/frontend/js/swiper.min.js"></script> <!-- Swiper for image and text sliders -->
    <script src="{{ asset('/') }}assets/frontend/js/purecounter.min.js"></script>
    <!-- Purecounter counter for statistics numbers -->
    <script src="{{ asset('/') }}assets/frontend/js/replaceme.min.js"></script> <!-- ReplaceMe for rotating text -->
    <script src="{{ asset('/') }}assets/frontend/js/scripts.js"></script> <!-- Custom scripts -->
    @yield('script')
</body>

</html>