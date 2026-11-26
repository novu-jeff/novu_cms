<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Committee Member Portal | Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card border-0 shadow-sm rounded-4" style="width: 100%; max-width: 420px;">
        <div class="card-body p-4 p-md-5">

            <div class="text-center mb-4">
                <h4 class="fw-bold mb-1">Committee Portal</h4>
                <p class="text-muted small">Standing Committee Member Access</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger small">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('committee-portal.login.submit') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           value="{{ old('email') }}"
                           required
                           autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password"
                           name="password"
                           class="form-control"
                           required>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input"
                           type="checkbox"
                           name="remember"
                           value="1"
                           id="remember">
                    <label class="form-check-label small" for="remember">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Sign In
                </button>
            </form>

        </div>
    </div>
</div>

</body>
</html>