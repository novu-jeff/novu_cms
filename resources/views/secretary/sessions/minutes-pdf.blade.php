
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Official Minutes of the Meeting</title>

    <style>
        @page {
            margin: 34px 42px 46px 42px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10.5px;
            line-height: 1.55;
            color: #243247;
        }

        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 3px solid #183b56;
            padding-bottom: 13px;
        }

        .logo-cell {
            width: 100px;
            vertical-align: middle;
        }

        .logo {
            width: 82px;
            height: 82px;
        }

        .header-text {
            text-align: center;
            vertical-align: middle;
        }

        .client-name {
            font-size: 17px;
            font-weight: bold;
            color: #183b56;
            margin: 0 0 3px;
        }

        .client-position {
            font-size: 11px;
            font-weight: bold;
            color: #526779;
            margin: 3px 0;
        }

        .header-text p {
            font-size: 9px;
            color: #526170;
            margin: 2px 0;
        }

        .document-title {
            text-align: center;
            margin: 18px 0 7px;
            font-size: 17px;
            font-weight: bold;
            color: #183b56;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .session-banner {
            text-align: center;
            background-color: #f1f6fa;
            border: 1px solid #d6e2eb;
            border-top: 3px solid #2d718c;
            padding: 12px 16px;
            margin: 0 0 20px;
        }

        .session-title {
            font-size: 13px;
            font-weight: bold;
            color: #243f55;
            margin: 0 0 4px;
        }

        .session-date {
            margin: 0;
            color: #5b6c7b;
            font-size: 10px;
        }

        h3 {
            font-size: 12px;
            color: #183b56;
            margin: 20px 0 10px;
            padding: 7px 10px;
            background-color: #edf3f7;
            border-left: 4px solid #2d718c;
            page-break-after: avoid;
        }

        h4 {
            font-size: 11px;
            color: #183b56;
            margin: 15px 0 8px;
            padding: 8px 10px;
            background-color: #f4f7f9;
            border: 1px solid #dce5eb;
            border-left: 4px solid #2d718c;
            page-break-after: avoid;
        }

        p {
            margin: 5px 0 9px;
        }

        .field-label {
            font-weight: bold;
            color: #344b5e;
        }

        .content {
            white-space: pre-wrap;
            overflow-wrap: break-word;
            color: #263746;
        }

        .vote-box {
            margin: 10px 0;
            padding: 10px 12px;
            border: 1px solid #d7e0e7;
            background-color: #fbfcfd;
            page-break-inside: avoid;
        }

        .vote-heading {
            font-weight: bold;
            color: #183b56;
            margin: 0 0 5px;
        }

        .vote-summary {
            width: 100%;
            border-collapse: collapse;
            margin-top: 7px;
        }

        .vote-summary th,
        .vote-summary td {
            border: 1px solid #d3dde5;
            padding: 6px 7px;
            text-align: center;
        }

        .vote-summary th {
            background-color: #eaf1f5;
            color: #29485d;
            font-weight: bold;
        }

        .vote-summary tbody tr:nth-child(even) td {
            background-color: #f8fafb;
        }

        .agenda-section {
            page-break-inside: avoid;
        }

        .signature-section {
            margin-top: 38px;
            width: 100%;
            page-break-inside: avoid;
        }

        .signature-cell {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 10px 20px;
        }

        .signature-line {
            border-top: 1px solid #71808b;
            padding-top: 7px;
            margin-top: 35px;
            color: #344b5e;
        }

        .muted {
            color: #687887;
        }
    </style>
</head>
<body>

    {{-- Official Header --}}
    
<table class="header">
    <tr>
        <td class="logo-cell">
            @if($organization?->logo_path)
                <img
                    src="{{ storage_path('app/public/' . $organization->logo_path) }}"
                    class="logo"
                    alt="Organization Logo"
                >
            @endif
        </td>

        <td class="header-text">
            <p class="client-name">
                {{ $organization?->organization_name ?? config('app.name') }}
            </p>

            @if($organization?->organization_subtitle)
                <p class="client-position">
                    {{ $organization->organization_subtitle }}
                </p>
            @endif

            @if($organization?->address)
                <p>{{ $organization->address }}</p>
            @endif

            @if($organization?->contact_information)
                <p>{{ $organization->contact_information }}</p>
            @endif
        </td>

        <td class="logo-cell"></td>
    </tr>
</table>


    {{-- Session Information --}}
    <div class="session-banner">
        <div class="document-title">
            Minutes of the Meeting
        </div>

        <div class="session-title">
            {{ $session->title }}
        </div>

        <p class="session-date">
            {{ $session->session_date
                ? \Carbon\Carbon::parse($session->session_date)->format('F d, Y')
                : 'Date not specified' }}
        </p>
    </div>

    {{-- Meeting Proceedings --}}
    <h3>Meeting Proceedings</h3>

    <p>
        <span class="field-label">Call to Order:</span><br>
        <span class="content">{{ $session->call_to_order ?: 'Not recorded.' }}</span>
    </p>

    <p>
        <span class="field-label">Prayer:</span><br>
        <span class="content">{{ $session->prayer ?: 'Not recorded.' }}</span>
    </p>

    <p>
        <span class="field-label">Roll Call:</span><br>
        <span class="content">{{ $session->roll_call ?: 'Not recorded.' }}</span>
    </p>

    @if(isset($attendance) && $attendance->isNotEmpty())
        <p class="field-label">Attendance Record:</p>

        <table class="vote-summary">
            <thead>
                <tr>
                    <th>Attendee</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendance as $attendee)
                    <tr>
                        <td>{{ $attendee->attendee_name }}</td>
                        <td>{{ $attendee->attendee_role }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Agenda Items --}}
    <h3>Agenda Items and Deliberations</h3>

    @forelse($documents as $doc)
        @php
            $minute = $doc->document
                ? ($minutes[$doc->document->id] ?? null)
                : null;

            $agendaVotes = isset($votesByAgenda)
                ? $votesByAgenda->get($doc->id, collect())
                : collect();
        @endphp

        <div class="agenda-section">
            <h4>
                {{ $doc->agenda_order }}.
                {{ $doc->document->title ?? 'Untitled Agenda Item' }}
            </h4>

            <p>
                <span class="field-label">Proponent:</span>
                {{ $doc->document->member->name ?? 'Member' }}
            </p>

            <p>
                <span class="field-label">Discussion:</span><br>
                <span class="content">{{ $minute->discussion ?? 'No discussion recorded.' }}</span>
            </p>

            @if($minute && $minute->official_reference)
                <p>
                    <span class="field-label">Official Reference:</span><br>
                    <span class="content">{{ $minute->official_reference }}</span>
                </p>
            @endif

            @if($minute && $minute->motion)
                <p>
                    <span class="field-label">Motion:</span><br>
                    <span class="content">{{ $minute->motion }}</span>
                </p>

                @if($minute->motion_moved_by)
                    <p>
                        <span class="field-label">Moved by:</span>
                        {{ $minute->motion_moved_by }}
                    </p>
                @endif

                @if($minute->motion_seconded_by)
                    <p>
                        <span class="field-label">Seconded by:</span>
                        {{ $minute->motion_seconded_by }}
                    </p>
                @endif
            @endif

            @if($agendaVotes->isNotEmpty())
                <p class="field-label">Recorded Voting Results</p>

                @foreach($agendaVotes as $vote)
                    @php
                        $result = $vote->result_data ?? [];
                    @endphp

                    <div class="vote-box">
                        <p class="vote-heading">
                            {{ $vote->motion_title ?: 'Motion' }}
                        </p>

                        @if($vote->motion_description)
                            <p>
                                <span class="field-label">Motion Details:</span>
                                {{ $vote->motion_description }}
                            </p>
                        @endif

                        <table class="vote-summary">
                            <thead>
                                <tr>
                                    <th>Yes</th>
                                    <th>No</th>
                                    <th>Abstain</th>
                                    <th>Total Votes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $result['yes'] ?? 0 }}</td>
                                    <td>{{ $result['no'] ?? 0 }}</td>
                                    <td>{{ $result['abstain'] ?? 0 }}</td>
                                    <td>{{ $result['total_votes'] ?? 0 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @endif

            <p>
                <span class="field-label">Decision:</span><br>
                <span class="content">{{ $minute->decision ?? 'No decision recorded.' }}</span>
            </p>
        </div>
    @empty
        <p class="muted">No agenda items recorded for this session.</p>
    @endforelse

    {{-- Committee Report Agenda Items --}}
@if($committeeReports->isNotEmpty())

    <h3>Committee Reports and Deliberations</h3>

    @foreach($committeeReports as $reportItem)

        @php
            $referral = $reportItem->referral;
            $committeeMinute = $committeeReportMinutes->get($reportItem->id);

            $committeeName = $referral?->committee?->name ?? 'Committee';

            $originalTitle = $referral?->sessionDocument?->document?->title
                ?? 'Original agenda document unavailable';
        @endphp

        <div class="agenda-section">

            <h4>
                {{ $reportItem->agenda_order }}.
                {{ $committeeName }} — Referral #{{ $reportItem->committee_referral_id }}
            </h4>

            <p>
                <span class="field-label">Original Agenda:</span>
                {{ $originalTitle }}
            </p>

            @if($referral?->recommendation)
                <p>
                    <span class="field-label">Committee Recommendation:</span><br>
                    <span class="content">{{ $referral->recommendation }}</span>
                </p>
            @endif

            <p>
                <span class="field-label">Official Reference:</span><br>
                <span class="content">
                    {{ $committeeMinute->official_reference ?? 'Not recorded.' }}
                </span>
            </p>

            <p>
                <span class="field-label">Discussion:</span><br>
                <span class="content">
                    {{ $committeeMinute->discussion ?? 'No discussion recorded.' }}
                </span>
            </p>

            @if($committeeMinute && $committeeMinute->motion)
                <p>
                    <span class="field-label">Motion:</span><br>
                    <span class="content">{{ $committeeMinute->motion }}</span>
                </p>

                @if($committeeMinute->motion_moved_by)
                    <p>
                        <span class="field-label">Moved by:</span>
                        {{ $committeeMinute->motion_moved_by }}
                    </p>
                @endif

                @if($committeeMinute->motion_seconded_by)
                    <p>
                        <span class="field-label">Seconded by:</span>
                        {{ $committeeMinute->motion_seconded_by }}
                    </p>
                @endif
            @endif

            <p>
                <span class="field-label">Decision / Disposition:</span><br>
                <span class="content">
                    {{ $committeeMinute->decision ?? 'No decision recorded.' }}
                </span>
            </p>

        </div>

    @endforeach

@endif

    {{-- Adjournment --}}
    <h3>Adjournment</h3>

    <p>
        <span class="field-label">Adjournment Time:</span>
        {{ $session->adjournment_time ?: 'Not recorded.' }}
    </p>

    {{-- Signature Blocks --}}
    <table class="signature-section">
        <tr>
            <td class="signature-cell">
                <div class="signature-line">
                    <strong>Prepared by</strong><br>
                    Secretary
                </div>
            </td>

            <td class="signature-cell">
                <div class="signature-line">
                    <strong>Approved by</strong><br>
                    {{ env('APP_CLIENT_POSITION') }}
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
