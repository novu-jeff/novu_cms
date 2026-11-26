
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Committee Dashboard - Committee Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          rel="stylesheet">

    <style>
        :root { --committee-ink:#172033; --committee-muted:#667085; --committee-line:#e7ebf2; --committee-primary:#3157d5; }
        body { background:#f6f8fb; color:var(--committee-ink); }
        .navbar { background:#248fc5 !important; box-shadow:0 3px 14px rgba(16,24,40,.08); }
        .navbar .nav-link { font-size: 12px; color:rgba(255,255,255,.72); padding:10px 14px; border-radius:9px; transition:all .18s ease; }
        .navbar .nav-link:hover,.navbar .nav-link.active { color:#fff; background:rgba(255,255,255,.12); }
        .page-header { background:#fff; color:var(--committee-ink); border:1px solid var(--committee-line); border-radius:16px; box-shadow:0 5px 20px rgba(16,24,40,.035); }
        .page-header .small { color:var(--committee-primary) !important; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
        .page-header h3 { font-size:clamp(1.5rem,2.4vw,2rem); font-weight:750; letter-spacing:-.035em; }
        .page-header p { color:var(--committee-muted) !important; }
        .stat-card,.workspace-card { border:1px solid var(--committee-line); border-radius:16px; box-shadow:0 5px 20px rgba(16,24,40,.035); overflow:hidden; }
        .stat-label { color:var(--committee-muted); font-size:.78rem; font-weight:700; }
        .stat-value { font-size:1.8rem; font-weight:750; letter-spacing:-.03em; color:var(--committee-ink); }
        .section-title { font-size:1rem; font-weight:750; margin-bottom:0; color:var(--committee-ink); }
        .committee-card { border:1px solid var(--committee-line); border-radius:14px; background:#fff; transition:all .18s ease; }
        .committee-card:hover { border-color:#b8c7fb; box-shadow:0 6px 20px rgba(16,24,40,.06); transform:translateY(-2px); }
        .committee-icon { width:42px;height:42px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#eef2ff;color:var(--committee-primary); }
        .meeting-row,.agenda-row { border-bottom:1px solid #eef1f5; padding:17px 0; }
        .meeting-row:last-child,.agenda-row:last-child { border-bottom:0; }
        .meeting-title { color:var(--committee-ink); font-weight:750; }
        .small-meta { color:var(--committee-muted); font-size:.82rem; }
        .status-count-card { min-height:105px; }
        .status-count { font-size:1.7rem;font-weight:750;color:var(--committee-ink); }
        .table thead th { background:#f8fafc;color:#667085;font-size:.72rem;font-weight:750;text-transform:uppercase;letter-spacing:.055em;white-space:nowrap;border-bottom:1px solid var(--committee-line); }
        .table td { font-size:.88rem;color:#475467;border-color:#eef1f5; }
        .empty-state { padding:38px 20px; }
        .empty-icon { font-size:2.25rem;color:#cbd5e1; }
        .pagination { margin-bottom:0; }
        .pagination .page-link { color:var(--committee-primary); }
        .pagination .active .page-link { background:var(--committee-primary);border-color:var(--committee-primary);color:#fff; }
        .dashboard-stat { height:100%; padding:1.15rem 1.25rem; border:1px solid var(--committee-line); border-radius:15px; background:#fff; box-shadow:0 5px 18px rgba(16,24,40,.035); }
        .dashboard-stat .stat-icon { width:2.6rem;height:2.6rem;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#eef2ff;color:var(--committee-primary);font-size:1.05rem; }
        .dashboard-stat .stat-number { font-size:1.8rem;font-weight:750;letter-spacing:-.03em;line-height:1.2;color:var(--committee-ink);margin-top:.9rem; }
        .dashboard-stat .stat-caption { color:var(--committee-muted);font-size:.8rem;margin-top:.25rem; }
        .card-header { border-bottom:1px solid var(--committee-line) !important; }
        .btn-outline-primary { --bs-btn-color:var(--committee-primary);--bs-btn-border-color:#c7d2fe;--bs-btn-hover-bg:var(--committee-primary);--bs-btn-hover-border-color:var(--committee-primary); }
        @media (max-width:767px) { .page-header { padding:1.25rem !important; } .dashboard-action,.meeting-action { width:100%; } }
    </style>
</head>

<body>

@include('committee-portal.partials.navbar')

<div class="container-fluid px-3 px-lg-4 py-4">

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="page-header p-4 mb-4 shadow-sm">
        <h3 class="fw-bold">
            {{ $signingRequest->document_title }}
        </h3>

        <div class="text-muted">
            Reference: {{ $signingRequest->reference_no }}
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">
            Signing Sequence
        </div>

        <div class="card-body">

            @foreach($signingRequest->signers as $signer)

                <div class="d-flex justify-content-between align-items-center border-bottom py-3">

                    <div>
                        <strong>
                            {{ $signer->user?->name }}
                        </strong>

                        <div class="small text-muted">
                            {{ $signer->signer_role }}
                            · Step {{ $signer->signing_order }}
                        </div>
                    </div>

                    @if($signer->status === 'signed')
                        <span class="badge bg-success">Signed</span>
                    @elseif((int) $signer->user_id === (int) $user->id && $isNextSigner)
                        <span class="badge bg-warning text-dark">Your Turn</span>
                    @else
                        <span class="badge bg-secondary">
                            {{ ucfirst($signer->status) }}
                        </span>
                    @endif

                </div>

            @endforeach

        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white fw-bold">
            Original Committee Report
        </div>

        <div class="card-body">

            <div class="d-flex flex-wrap gap-2 mb-3">

                <a href="{{ route(
                    'committee-portal.signing.preview',
                    $signingRequest
                ) }}"
                   target="_blank"
                   class="btn btn-outline-primary">

                    <i class="fas fa-eye me-1"></i>
                    Preview PDF

                </a>

                @if($signingRequest->status === 'fully_signed')
                    <a href="{{ route(
                        'committee-portal.signing.download',
                        $signingRequest
                    ) }}"
                       class="btn btn-success">

                        <i class="fas fa-download me-1"></i>
                        Download Signed PDF

                    </a>
                @endif

            </div>

            <iframe
                src="{{ route(
                    'committee-portal.signing.preview',
                    $signingRequest
                ) }}"
                width="100%"
                height="600"
                style="border:1px solid #ddd;border-radius:8px">
            </iframe>

        </div>
    </div>

    @if($isNextSigner && in_array($signingRequest->status, ['pending', 'partially_signed'], true))

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white fw-bold">
                <i class="fas fa-file-signature text-primary me-2"></i>
                Sign Committee Report
            </div>

            <div class="card-body">

                <div class="alert alert-info">
                    You are the authorized next signer.
                    Please review the document before signing.
                </div>

                <form method="POST"
                      action="{{ route(
                        'committee-portal.signing.sign',
                        $signingRequest
                      ) }}"
                      enctype="multipart/form-data">

                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Committee Portal Password
                        </label>

                        <input type="password"
                               name="password"
                               class="form-control"
                               required
                               autocomplete="current-password">

                        @error('password')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Signature Image (Optional PNG)
                        </label>

                        <input type="file"
                               name="signature"
                               class="form-control"
                               accept="image/png">

                        <small class="text-muted">
                            You may upload a PNG signature image.
                        </small>

                        @error('signature')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-check mb-3">

                        <input class="form-check-input"
                               type="checkbox"
                               name="consent"
                               value="1"
                               id="consent"
                               required>

                        <label class="form-check-label"
                               for="consent">

                            I confirm that I have reviewed this document
                            and authorize the recording of my electronic signature.

                        </label>

                        @error('consent')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <button type="submit"
                            class="btn btn-primary"
                            onclick="return confirm('Are you sure you want to sign this committee report?')">

                        <i class="fas fa-signature me-1"></i>
                        Sign Committee Report

                    </button>

                </form>

            </div>
        </div>

    @elseif($signingRequest->status === 'fully_signed')

        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            All required signers have completed this document.
        </div>

    @else

        <div class="alert alert-secondary">
            Your signature is not currently required.
            The previous signer must complete their step first.
        </div>

    @endif

</div>

</body>
</html>