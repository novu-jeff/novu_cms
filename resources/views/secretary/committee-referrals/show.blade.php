
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <a
            href="{{ route('secretary.committee-referrals.index') }}"
            class="text-decoration-none"
        >
            &larr; Back to referrals
        </a>
        <h1 class="h3 mt-3 mb-1">Referral #{{ $referral->id }}</h1>
        <p class="text-muted mb-0">
            {{ $referral->committee?->name ?? 'Unknown committee' }}
        </p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $document = $referral->sessionDocument?->document;
    @endphp

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h5">Referral Details</h2>

                    <dl class="row mb-0">
                        <dt class="col-sm-5">Agenda Item</dt>
                        <dd class="col-sm-7">
                            {{ $document->title ?? $document->subject ?? 'Agenda item #' . $referral->session_document_id }}
                        </dd>

                        <dt class="col-sm-5">Committee</dt>
                        <dd class="col-sm-7">
                            {{ $referral->committee?->name ?? 'Unknown committee' }}
                        </dd>

                        <dt class="col-sm-5">Status</dt>
                        <dd class="col-sm-7">
                            {{ ucfirst(str_replace('_', ' ', $referral->status)) }}
                        </dd>

                        <dt class="col-sm-5">Referred On</dt>
                        <dd class="col-sm-7">
                            {{ $referral->referred_at?->format('M d, Y h:i A') ?? '—' }}
                        </dd>
                    </dl>

                    <hr>

                    <h3 class="h6">Referral Remarks</h3>
                    <p class="mb-0" style="white-space: pre-wrap">{{ $referral->referral_remarks ?: 'No remarks provided.' }}</p>

                    @if ($referral->status === 'referred')
                        <form
                            method="POST"
                            action="{{ route('secretary.committee-referrals.start-review', $referral->id) }}"
                            class="mt-4"
                        >
                            @csrf
                            <button class="btn btn-outline-primary">
                                Mark Under Review
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h5">Committee Recommendation</h2>

                    @if ($referral->recommendation)
                        <p style="white-space: pre-wrap">{{ $referral->recommendation }}</p>
                    @else
                        <p class="text-muted">No recommendation submitted yet.</p>
                    @endif

                    
                    @php
                        $meetingReport = $referral->meetingReports()
                            ->with('meeting')
                            ->latest('committee_meeting_reports.id')
                            ->first();

                        $hasIndividualReport = $meetingReport
                            && $meetingReport->meeting
                            && $meetingReport->meeting->status === 'completed'
                            && $meetingReport->referrals()->count() === 1
                            && (int) $meetingReport->referrals()->value('committee_referrals.id')
                                === (int) $referral->id;

                        $hasReport = $referral->report_file_path || $hasIndividualReport;
                    @endphp

                    @php
                        $linkedReferralCount = $meetingReport?->referrals?->count() ?? 0;
                        $isIndividualReport = $linkedReferralCount === 1;
                    @endphp

                    @if ($hasReport)

                        @if ($referral->report_file_path)
                            {{-- Individual Committee Report --}}
                            <a
                                class="btn btn-outline-secondary btn-sm"
                                href="{{ route('secretary.committee-referrals.report', $referral->id) }}"
                            >
                                <i class="fas fa-file-pdf me-1"></i>
                                Download Original Committee Report
                            </a>
                        @elseif ($hasIndividualReport && $meetingReport)
                            {{-- Committee Meeting Report --}}
                            <a
                                class="btn btn-outline-secondary btn-sm"
                                href="{{ route('secretary.committee-meetings.report-file', $meetingReport->committee_meeting_id) }}"
                            >
                                <i class="fas fa-file-pdf me-1"></i>
                                Download Original Committee Report
                            </a>
                        @endif

                        @php
                            $committeeSigningRequest = \App\Models\DocumentSigningRequest::query()
                                ->where('document_type', 'committee_report')
                                ->where('document_id', $referral->id)
                                ->latest('id')
                                ->first();
                        @endphp

                        @if ($referral->status === 'report_submitted')

                            @if ($committeeSigningRequest?->status === 'fully_signed')

                                <a
                                    href="{{ route('document-signing.download', $committeeSigningRequest) }}"
                                    class="btn btn-success btn-sm"
                                >
                                    <i class="fas fa-download me-1"></i>
                                    Download Final Signed Report
                                </a>

                            @elseif (in_array($committeeSigningRequest?->status, [
                                'draft',
                                'pending',
                                'partially_signed',
                            ], true))

                                <a
                                    href="{{ route('document-signing.show', $committeeSigningRequest) }}"
                                    class="btn btn-info btn-sm"
                                >
                                    <i class="fas fa-eye me-1"></i>
                                    View Signing Progress
                                </a>

                            @else

                                <form
                                    method="POST"
                                    action="{{ route('secretary.committee-referrals.submit-for-signing', $referral->id) }}"
                                    class="d-inline"
                                >
                                    @csrf

                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-file-signature me-1"></i>
                                        Submit for Electronic Signing
                                    </button>
                                </form>

                            @endif

                        @endif

                    @endif

                    @if ($referral->meetingReports->isNotEmpty())
                        <hr>

                        <h3 class="h6">
                            <i class="fas fa-file-pdf text-danger me-1"></i>
                            {{ $isIndividualReport
                            ? 'Individual Committee Report'
                            : 'Consolidated Committee Meeting Report' }}
                        </h3>

                        <p class="small text-muted">
                            This referral was included in the following completed committee meeting report(s).
                        </p>

                        @foreach ($referral->meetingReports as $meetingReport)
                            <div class="border rounded p-3 mb-2">
                                <div class="fw-semibold">
                                    {{ $meetingReport->meeting?->title ?? 'Committee Meeting' }}
                                </div>

                                <div class="small text-muted mb-2">
                                    Uploaded:
                                    {{ $meetingReport->uploaded_at?->format('F d, Y h:i A') ?? 'Not recorded' }}
                                </div>

                                <a
                                    href="{{ route('secretary.committee-meetings.report-file', $meetingReport->committee_meeting_id) }}"
                                    class="btn btn-outline-danger btn-sm"
                                    target="_blank"
                                >
                                    <i class="fas fa-download me-1"></i>
                                    {{ $isIndividualReport
                                    ? 'Download Individual Committee Report'
                                    : 'Download Consolidated PDF' }}
                                </a>
                            </div>
                        @endforeach
                    @endif

                    @if ($referral->status === 'returned_to_council')
                        <a
                            href="{{ route('secretary.committee-referrals.add-to-agenda', $referral->id) }}"
                            class="btn btn-primary btn-sm ms-2"
                        >
                            <i class="fa-solid fa-calendar-plus me-2"></i>
                            Add to Future Agenda
                        </a>
                    @endif
                </div>
            </div>

            @if (in_array($referral->status, ['referred', 'under_review'], true))
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h2 class="h5">Submit Committee Report</h2>
                        <p class="small text-muted">
                            Upload the signed and finalized committee report as a PDF.
                            Once submitted, the report will be forwarded for council review.
                        </p>

                        <form
                            method="POST"
                            enctype="multipart/form-data"
                            action="{{ route('secretary.committee-referrals.submit-report', $referral->id) }}"
                        >
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Recommendation</label>
                                <textarea
                                    name="recommendation"
                                    class="form-control"
                                    rows="5"
                                    maxlength="10000"
                                    required
                                >{{ old('recommendation', $referral->recommendation) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Committee Report (PDF)</label>
                                <input
                                    type="file"
                                    name="report"
                                    class="form-control"
                                    accept=".pdf,application/pdf"
                                    required
                                >
                                <div class="form-text">Maximum file size: 10 MB.</div>
                            </div>

                            <button class="btn btn-primary">
                                Submit Report
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            @if ($referral->status === 'report_submitted')
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h5">Return to Council</h2>
                        <p class="text-muted">
                            Confirm that the report has been reviewed before returning
                            the referral to the full council.
                        </p>

                        <form
                            method="POST"
                            action="{{ route('secretary.committee-referrals.return-to-council', $referral->id) }}"
                        >
                            @csrf
                            <button
                                class="btn btn-success"
                                onclick="return confirm('Return this committee report to the full council?')"
                            >
                                Accept Report and Return to Council
                            </button>
                        </form>
                    </div>
                </div>
            @elseif ($referral->status === 'returned_to_council')
                <div class="alert alert-success">
                    This report has been returned to the full council and is ready
                    for scheduling for deliberation.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
