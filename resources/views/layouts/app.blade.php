<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">

    <!-- ✅ DataTables 1.13.6 Bootstrap 5 CSS (compatible with jQuery and Bootstrap 5) -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/fancybox/fancybox.css"
        />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @yield('styles')

    @vite(['resources/sass/app.scss', 'resources/js/app.js', 'resources/sass/style.scss'])
</head>
<body>
    <div id="app">
        <div class="loading d-none" id="isLoading">
            <div class="d-flex justify-content-center">
                <div class="spinner-border text-light" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

        @include('components.sidebar')

        <main>
            @include('components.navbar')
            @yield('content')
        </main>
    </div>

    <!-- jQuery 3.6 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Bootstrap Bundle JS (only include once) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- ✅ DataTables 1.13.6 core + Bootstrap 5 integration (compatible with jQuery) -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/fancybox/fancybox.umd.js"></script>

    @yield('scripts')
</body>
</html>
