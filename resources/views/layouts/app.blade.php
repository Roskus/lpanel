<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <!--<script src="{{ asset('js/app.js') }}" defer></script>-->

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito">

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
          integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('/asset/theme/sb-admin-2/css/sb-admin-2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/asset/css/panel.css') }}">
    <link rel="stylesheet" href="{{ asset('/asset/vendor/fontawesome/5.15.2/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/asset/vendor/devicon/2.10.1/devicon.min.css') }}">
</head>
<body @if(Route::current()->getName() === 'login') class="bg-gradient-primary" @endif>
<div id="app">
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
        @include('navbar')
    </nav>

    <main class="container-fluid px-0">
    <div class="row no-gutters">
        @auth
        <div class="col-2">
            @include('sidebar')
        </div>
        @endauth
        <div class="@auth col-10 @else col-12 @endauth py-4">
            @yield('content')
            @if(Route::current()->getName() != 'login')
            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Powered by Roskus</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->
            @endif
        </div>
    </div>
    </main>
</div><!--./app-->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>
