
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

    <div class="page-header p-4 mb-4 shadow-sm">
        <h3 class="fw-bold text-dark">
            <i class="fas fa-file-signature text-primary me-2"></i>
            Electronic Signing
        </h3>

        <p class="text-muted mb-0">
            Review and sign committee reports assigned to you.
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <strong>Assigned Signing Requests</strong>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>Reference</th>
                            <th>Document</th>
                            <th>Signing Progress</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($requests as $item)

                        @php
                            $signedCount = $item->signers
                                ->where('status', 'signed')
                                ->count();

                            $totalSigners = $item->signers->count();

                            $statusClass = match($item->status) {
                                'pending' => 'bg-warning text-dark',
                                'partially_signed' => 'bg-info text-dark',
                                'fully_signed' => 'bg-success',
                                'rejected' => 'bg-danger',
                                'revoked' => 'bg-secondary',
                                default => 'bg-light text-dark',
                            };
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ $item->reference_no }}</strong>
                            </td>

                            <td>
                                {{ $item->document_title }}

                                <div class="small text-muted">
                                    {{ ucwords(str_replace('_', ' ', $item->document_type)) }}
                                </div>
                            </td>

                            <td>
                                {{ $signedCount }} / {{ $totalSigners }} signed
                            </td>

                            <td>
                                <span class="badge {{ $statusClass }}">
                                    {{ ucwords(str_replace('_', ' ', $item->status)) }}
                                </span>
                            </td>

                            <td class="text-end">
                                <a href="{{ route(
                                    'committee-portal.signing.show',
                                    $item
                                ) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-folder-open me-1"></i>
                                    View
                                </a>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="5"
                                class="text-center text-muted py-5">

                                <i class="fas fa-file-circle-check fa-2x mb-3"></i>

                                <div class="fw-semibold">
                                    No assigned signing requests
                                </div>

                                <small>
                                    Documents requiring your signature will appear here.
                                </small>

                            </td>
                        </tr>

                    @endforelse

                    </tbody>
                </table>
            </div>

            {{ $requests->links() }}

        </div>
    </div>

</div>

</body>
</html>