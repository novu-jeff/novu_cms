<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $productLabel ?? $productName }} | White Paper</title>
    @vite(['resources/sass/whitepaper.scss'])
</head>
<body class="whitepaper-body">
    <nav class="wp-nav">
        <div class="container">
            <a class="wp-brand" href="{{ $homeRoute }}">
                @if(config('app.logo'))
                    <img src="{{ asset('default/' . config('app.logo')) }}" alt="{{ $productName }}">
                @endif
                <span>{{ $productName }}</span>
            </a>
            <ul class="wp-nav-links">
                <li><a href="{{ $homeRoute }}">Home</a></li>
                <li><a href="{{ route('whitepaper') }}" class="active">White Paper</a></li>
                <li><a href="{{ $loginRoute }}" class="wp-btn-login">{{ $loginLabel ?? 'Login' }}</a></li>
            </ul>
        </div>
    </nav>

    <header class="wp-hero">
        <div class="container">
            @yield('hero')
        </div>
    </header>

    <main class="wp-main">
        <div class="container wp-layout">
            <aside class="wp-toc">
                <h3>On this page</h3>
                <ol>
                    @yield('toc')
                </ol>
            </aside>
            <div class="wp-content">
                @yield('content')
            </div>
        </div>
    </main>

    <section class="wp-cta">
        <div class="container">
            @yield('cta')
        </div>
    </section>

    <footer class="wp-footer">
        Copyright &copy; {{ date('Y') }} | {{ $productName }} &middot; novulutions.com
    </footer>
</body>
</html>
