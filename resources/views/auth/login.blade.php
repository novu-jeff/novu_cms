@extends('layouts.auth')

@section('content')
<div class="login-page min-vh-100 d-flex align-items-center justify-content-center px-3 py-5">
    <div class="login-card card border-0 shadow-lg">
        <div class="card-body p-4 p-md-5">

            {{-- Logo and Heading --}}
            <div class="text-center mb-4">
                <div class="login-logo mb-4">
                    <img
                        src="{{ asset('default/cms_nav.png') }}"
                        alt="CMS Logo"
                        class="img-fluid"
                    >
                </div>

                <h3 class="fw-bold mb-2">Welcome Back</h3>
                <p class="text-muted mb-0">Sign in to access the CMS portal.</p>
            </div>

            {{-- Session Status --}}
            @if (session('status'))
                <div class="alert alert-success rounded-3" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">
                        Username / Email
                    </label>

                    <div class="input-group login-input-group">
                        <span class="input-group-text">
                            <i class="fa-solid fa-user"></i>
                        </span>

                        <input
                            type="text"
                            class="form-control @error('email') is-invalid @enderror"
                            id="email"
                            name="email"
                            placeholder="Enter your username or email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                            autofocus
                        >
                    </div>

                    @error('email')
                        <div class="text-danger small mt-2" role="alert">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">
                        Password
                    </label>

                    <div class="input-group login-input-group">
                        <span class="input-group-text">
                            <i class="fa-solid fa-lock"></i>
                        </span>

                        <input
                            type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >

                        <button
                            class="input-group-text password-toggle"
                            type="button"
                            id="togglePassword"
                            aria-label="Show password"
                            aria-pressed="false"
                        >
                            <i class="fa-solid fa-eye" id="passwordIcon"></i>
                        </button>
                    </div>

                    @error('password')
                        <div class="text-danger small mt-2" role="alert">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary login-button">
                        <span>Sign In</span>
                        <i class="fa-solid fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('whitepaper') }}" class="whitepaper-link">
                    <i class="fa-regular fa-file-lines me-1"></i>
                    White Paper
                </a>
            </div>

            <div class="login-footer text-center mt-4 pt-3">
                <small class="text-muted">
                    Municipal CMS Portal
                </small>
            </div>

        </div>
    </div>
</div>

<style>
    .login-page {
        background:
            radial-gradient(circle at 15% 15%, rgba(255, 255, 255, 0.15), transparent 30%),
            radial-gradient(circle at 85% 85%, rgba(13, 110, 253, 0.18), transparent 35%),
            linear-gradient(135deg, #0b1f42 0%, #123c73 50%, #0d6efd 100%);
    }

    .login-card {
        width: 100%;
        max-width: 440px;
        border-radius: 22px;
        overflow: hidden;
        animation: loginFadeIn 0.45s ease-out;
    }

    .login-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 65px;
    }

    .login-logo img {
        max-height: 65px;
        max-width: 220px;
        object-fit: contain;
    }

    .login-card h3 {
        color: #172b4d;
        letter-spacing: -0.5px;
    }

    .login-input-group {
        min-height: 52px;
    }

    .login-input-group .input-group-text {
        background: #f5f7fb;
        border-color: #dee4ee;
        color: #64748b;
        padding-left: 15px;
        padding-right: 15px;
    }

    .login-input-group .form-control {
        min-height: 52px;
        border-color: #dee4ee;
        font-size: 0.95rem;
        box-shadow: none;
    }

    .login-input-group .form-control:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
        z-index: 3;
    }

    .login-input-group:focus-within .input-group-text {
        border-color: #86b7fe;
    }

    .login-input-group .form-control::placeholder {
        color: #a0aabb;
        font-size: 0.88rem;
    }

    .login-input-group .form-control.is-invalid {
        z-index: 3;
    }

    .password-toggle {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .password-toggle:hover {
        background: #e9eef7;
    }

    .login-button {
        min-height: 50px;
        border-radius: 12px;
        font-weight: 600;
        letter-spacing: 0.2px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .login-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 7px 18px rgba(13, 110, 253, 0.22);
    }

    .whitepaper-link {
        color: #0d6efd;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .whitepaper-link:hover {
        text-decoration: underline;
    }

    .login-footer {
        border-top: 1px solid #edf0f5;
    }

    @keyframes loginFadeIn {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 480px) {
        .login-card {
            border-radius: 18px;
        }

        .login-card .card-body {
            padding: 1.5rem !important;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .login-card {
            animation: none;
        }

        .login-button {
            transition: none;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('togglePassword');
        const passwordIcon = document.getElementById('passwordIcon');

        if (!passwordInput || !toggleButton || !passwordIcon) {
            return;
        }

        toggleButton.addEventListener('click', function () {
            const isPasswordHidden = passwordInput.type === 'password';

            passwordInput.type = isPasswordHidden ? 'text' : 'password';
            passwordIcon.classList.toggle('fa-eye', !isPasswordHidden);
            passwordIcon.classList.toggle('fa-eye-slash', isPasswordHidden);

            toggleButton.setAttribute(
                'aria-label',
                isPasswordHidden ? 'Hide password' : 'Show password'
            );

            toggleButton.setAttribute(
                'aria-pressed',
                isPasswordHidden ? 'true' : 'false'
            );
        });
    });
</script>
@endsection
