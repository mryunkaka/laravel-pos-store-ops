<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistem kasir dan manajemen toko.">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0d6efd">
    <title>{{ optional(\App\Models\StoreSetting::current())->store_name ?: config('app.name', 'POS Dash') }}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ optional(\App\Models\StoreSetting::current())->logo ? asset('storage/' . \App\Models\StoreSetting::current()->logo) : asset('assets/images/favicon.ico') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/backend-plugin.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/backend.css?v=1.0.1') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css?v=1.0.1') }}">

    @yield('specificpagestyles')
</head>

<body>
    <!-- loader Start -->
    {{-- <div id="loading">
        <div id="loading-center"></div>
    </div> --}}
    <!-- loader END -->

    <!-- Wrapper Start -->
    <div class="wrapper">
        @include('dashboard.body.sidebar')

        @include('dashboard.body.navbar')

        <div class="content-page">
            @yield('container')
        </div>
    </div>
    <!-- Wrapper End-->

    @include('dashboard.body.footer')

    <!-- Backend Bundle JavaScript -->
    <script src="{{ asset('assets/js/backend-bundle.min.js') }}"></script>

    @yield('specificpagescripts')

    <!-- App JavaScript -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>

</html>
