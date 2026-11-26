
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Committee Deliberation Report</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 25mm 18mm 22mm 18mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10px;
            line-height: 1.55;
            color: #222;
        }

        .header {
            width: 100%;
            border-bottom: 3px double #243b53;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .seal {
            width: 85px;
            height: 85px;
            border: 2px solid #b08d35;
            border-radius: 50%;
            text-align: center;
            vertical-align: middle;
            color: #243b53;
            font-size: 9px;
            font-weight: bold;
            padding: 15px 5px;
        }

        .header-title {
            text-align: center;
            vertical-align: middle;
        }

        .header-title .republic {
            font-size: 9px;
            text-transform: uppercase;
        }

        .header-title .province {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-title .municipality {
            font-size: 17px;
            font-weight: bold;
            color: #243b53;
            text-transform: uppercase;
        }

        .header-title .office {
            font-size: 10px;
            font-weight: bold;
            margin-top: 3px;
        }

        .report-heading {
            text-align: center;
            margin: 25px 0 20px;
        }

        .report-heading h1 {
            font-size: 16px;
            color: #243b53;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .report-heading h2 {
            font-size: 12px;
            margin: 6px 0;
            text-transform: uppercase;
        }

        .section-title {
            background: #243b53;
            color: #fff;
            padding: 7px 9px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 17px 0 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 5px 7px;
            border: 1px solid #d5dce3;
        }

        .info-label {
            width: 25%;
            background: #f0f3f6;
            font-weight: bold;
        }

        .attendance-table th,
        .attendance-table td,
        .agenda-table th,
        .agenda-table td {
            border: 1px solid #cbd2d9;
            padding: 6px;
            vertical-align: top;
        }

        .attendance-table th,
        .agenda-table th {
            background: #e9eef3;
            color: #243b53;
            font-size: 9px;
            text-align: left;
        }

        .center {
            text-align: center;
        }

        .status {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
        }

        .agenda-block {
            border: 1px solid #cbd2d9;
            margin-bottom: 13px;
            page-break-inside: avoid;
        }

        .agenda-header {
            background: #edf1f5;
            padding: 9px;
            border-bottom: 1px solid #cbd2d9;
        }

        .agenda-content {
            padding: 10px;
        }

        .agenda-number {
            color: #b08d35;
            font-weight: bold;
        }

        .document-title {
            font-size: 11px;
            font-weight: bold;
            color: #243b53;
        }

        .action-block {
            border-left: 3px solid #b08d35;
            padding: 7px 10px;
            margin: 9px 0;
            background: #fafbfc;
            page-break-inside: avoid;
        }

        .action-title {
            font-weight: bold;
            color: #243b53;
        }

        .muted {
            color: #666;
            font-size: 8px;
        }

        .recommendation {
            background: #f5f7f9;
            border: 1px solid #d5dce3;
            padding: 8px;
            margin-top: 7px;
        }

        .certification {
            margin-top: 25px;
            line-height: 1.7;
            text-align: justify;
        }

        .signature-table {
            width: 100%;
            margin-top: 55px;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 15px;
        }

        .signature-line {
            border-top: 1px solid #222;
            padding-top: 7px;
            margin-top: 35px;
        }

        .signature-name {
            font-weight: bold;
            text-transform: uppercase;
        }

        .signature-position {
            font-size: 9px;
        }

        .certification-section {
            page-break-inside: avoid;
        }

        .signature-block {
            page-break-inside: avoid;
        }

        .footer {
            position: fixed;
            bottom: -14mm;
            left: 0;
            right: 0;
            border-top: 1px solid #b08d35;
            padding-top: 5px;
            font-size: 8px;
            color: #666;
        }

        .footer-table {
            width: 100%;
        }

        .page-number:after {
            content: counter(page);
        }

        .page-count:after {
            content: counter(pages);
        }

        .no-records {
            color: #777;
            font-style: italic;
            padding: 10px;
        }

        .small-note {
            font-size: 8px;
            color: #666;
        }

        .disposition {
            font-weight: bold;
            color: #243b53;
        }

        .clearfix {
            clear: both;
        }
    </style>
</head>

<body>

@php
    $agendaItems = $meeting->agendaItems->sortBy('agenda_order');

    $committeeName = $meeting->committee->name ?? 'Committee';

    $presentCount = $meeting->attendances
        ->where('attendance_status', 'present')->count();

    $lateCount = $meeting->attendances
        ->where('attendance_status', 'late')->count();

    $absentCount = $meeting->attendances
        ->where('attendance_status', 'absent')->count();

    $excusedCount = $meeting->attendances
        ->where('attendance_status', 'excused')->count();

    $totalAttendance = $meeting->attendances->count();

    $finalizedCount = $agendaItems->filter(function ($item) {
        return in_array($item->status, [
            'completed',
            'deferred',
            'returned_for_revision',
            'for_further_study',
        ]);
    })->count();

    $formatStatus = function ($status) {
        return ucwords(str_replace('_', ' ', $status));
    };
@endphp

{{-- FORMAL LETTERHEAD --}}

<div class="header">

    <table class="header-table">
        <tr>

            <td width="18%" align="center" valign="middle">
                <div class="seal">
                    MUNICIPAL<br>
                    SEAL<br>
                    <span style="font-size:7px;">PLACEHOLDER</span>
                </div>
            </td>

            <td width="64%" class="header-title">

                <div class="republic">
                    Republic of the Philippines
                </div>

                <div class="province">
                    Province of __________________
                </div>

                <div class="municipality">
                    Municipality of __________________
                </div>

                <div class="office">
                    OFFICE OF THE SANGGUNIANG BAYAN
                </div>

            </td>

            <td width="18%" align="center" valign="middle">
                <div style="font-size:8px;color:#777;">
                    OFFICIAL<br>
                    GOVERNMENT<br>
                    RECORD
                </div>
            </td>

        </tr>
    </table>

</div>

{{-- REPORT TITLE --}}

<div class="report-heading">

    <h1>Committee Deliberation Report</h1>

    <h2>{{ $committeeName }}</h2>

    <div class="muted">
        Official Record of Committee Proceedings and Recommendations
    </div>

</div>

{{-- I. MEETING INFORMATION --}}

<div class="section-title">
    I. Meeting Information
</div>

<table class="info-table">

    <tr>
        <td class="info-label">Meeting Title</td>
        <td colspan="3">{{ $meeting->title }}</td>
    </tr>

    <tr>
        <td class="info-label">Committee</td>
        <td colspan="3">{{ $committeeName }}</td>
    </tr>

    <tr>
        <td class="info-label">Meeting Date</td>
        <td>
            {{ $meeting->meeting_date?->format('F d, Y') }}
        </td>

        <td class="info-label">Status</td>
        <td>{{ strtoupper($meeting->status) }}</td>
    </tr>

    <tr>
        <td class="info-label">Start Time</td>
        <td>
            {{ $meeting->started_at?->format('h:i A') ?? 'Not recorded' }}
        </td>

        <td class="info-label">End Time</td>
        <td>
            {{ $meeting->ended_at?->format('h:i A') ?? 'Not recorded' }}
        </td>
    </tr>

    <tr>
        <td class="info-label">Venue</td>
        <td colspan="3">
            {{ $meeting->venue ?? 'Not specified' }}
        </td>
    </tr>

    <tr>
        <td class="info-label">Presiding Officer</td>
        <td colspan="3">
            {{ $meeting->presiding_officer ?? 'Not specified' }}
        </td>
    </tr>

</table>

{{-- II. ATTENDANCE --}}

<div class="section-title">
    II. Attendance and Quorum Certification
</div>

<table class="info-table" style="margin-bottom:10px;">

    <tr>
        <td class="info-label">Required Quorum</td>
        <td>{{ $requiredQuorum ?? 'Not specified' }}</td>

        <td class="info-label">Members Present</td>
        <td>{{ $presentCount + $lateCount }}</td>
    </tr>

    <tr>
        <td class="info-label">Quorum Result</td>
        <td colspan="3">
            <strong>
                {{ $quorumMet ? 'QUORUM ACHIEVED' : 'QUORUM NOT ACHIEVED' }}
            </strong>
        </td>
    </tr>

</table>

<table class="attendance-table">

    <thead>
        <tr>
            <th width="7%">No.</th>
            <th width="35%">Name</th>
            <th width="23%">Position</th>
            <th width="17%">Attendance</th>
            <th width="18%">Remarks</th>
        </tr>
    </thead>

    <tbody>

        @forelse($meeting->attendances as $index => $attendance)

            <tr>
                <td class="center">{{ $index + 1 }}</td>

                <td>{{ $attendance->attendee_name }}</td>

                <td>{{ $attendance->attendee_role ?? 'Member' }}</td>

                <td class="status">
                    {{ strtoupper($attendance->attendance_status) }}
                </td>

                <td>{{ $attendance->remarks ?? '-' }}</td>
            </tr>

        @empty

            <tr>
                <td colspan="5" class="center">
                    No attendance records available.
                </td>
            </tr>

        @endforelse

    </tbody>

</table>

<p class="small-note">
    Present: {{ $presentCount }} |
    Late: {{ $lateCount }} |
    Absent: {{ $absentCount }} |
    Excused: {{ $excusedCount }} |
    Total Recorded: {{ $totalAttendance }}
</p>

{{-- III. AGENDA AND DELIBERATION --}}

<div class="section-title">
    III. Agenda and Committee Deliberations
</div>

@forelse($agendaItems as $index => $item)

    @php
        $document = $item->referral?->sessionDocument?->document;

        $actions = $item->actions->sortBy('acted_at');

        $finalAction = $actions->last();

        $referral = $item->referral;
    @endphp

    <div class="agenda-block">

        <div class="agenda-header">

            <span class="agenda-number">
                AGENDA ITEM {{ $item->agenda_order }}
            </span>

            <br>

            <span class="document-title">
                {{ $document?->title ?? 'Document unavailable' }}
            </span>

            @if($referral)
                <div class="muted">
                    Referral Reference: #{{ $referral->id }}
                </div>
            @endif

        </div>

        <div class="agenda-content">

            <table class="info-table">

                <tr>
                    <td class="info-label">Final Disposition</td>

                    <td class="disposition">
                        {{ $formatStatus($item->status) }}
                    </td>
                </tr>

                <tr>
                    <td class="info-label">Deliberation Records</td>

                    <td>{{ $actions->count() }}</td>
                </tr>

            </table>

            <p>
                <strong>Proceedings and Deliberation History</strong>
            </p>

            @forelse($actions as $actionIndex => $action)

                <div class="action-block">

                    <div class="action-title">
                        @if($actionIndex === $actions->count() - 1)
                            Final Committee Action — {{ $formatStatus($action->action) }}
                        @else
                            Deliberation Record {{ $actionIndex + 1 }}
                        @endif
                    </div>

                    <div class="muted">
                        Recorded by:
                        {{ $action->actor?->name ?? 'Unknown user' }}

                        |

                        {{ $action->acted_at?->format('F d, Y h:i A') ?? 'Date not recorded' }}
                    </div>

                    @if($action->reason)

                        <p>
                            <strong>Discussion / Reason:</strong><br>
                            {{ $action->reason }}
                        </p>

                    @endif

                    @if($action->recommendation)

                        <div class="recommendation">

                            <strong>Committee Recommendation:</strong>

                            <p style="margin:5px 0 0;">
                                {{ $action->recommendation }}
                            </p>

                        </div>

                    @endif

                </div>

            @empty

                <div class="no-records">
                    No deliberation history recorded.
                </div>

            @endforelse

            @if($finalAction)

                <div class="recommendation">

                    <strong>Final Committee Action:</strong>

                    {{ $formatStatus($finalAction->action) }}

                    @if($finalAction->recommendation)

                        <p>
                            <strong>Final Recommendation:</strong><br>
                            {{ $finalAction->recommendation }}
                        </p>

                    @endif

                </div>

            @endif

        </div>

    </div>

@empty

    <p class="no-records">
        No agenda items recorded for this meeting.
    </p>

@endforelse

{{-- IV. SUMMARY --}}

<div class="section-title">
    IV. Summary of Committee Proceedings
</div>

<table class="info-table">

    <tr>
        <td class="info-label">Total Agenda Items</td>
        <td>{{ $agendaItems->count() }}</td>
    </tr>

    <tr>
        <td class="info-label">Completed</td>
        <td>
            {{ $agendaItems->where('status', 'completed')->count() }}
        </td>
    </tr>

    <tr>
        <td class="info-label">Deferred</td>
        <td>
            {{ $agendaItems->where('status', 'deferred')->count() }}
        </td>
    </tr>

    <tr>
        <td class="info-label">Returned for Revision</td>
        <td>
            {{ $agendaItems->where('status', 'returned_for_revision')->count() }}
        </td>
    </tr>

    <tr>
        <td class="info-label">For Further Study</td>
        <td>
            {{ $agendaItems->where('status', 'for_further_study')->count() }}
        </td>
    </tr>

    <tr>
        <td class="info-label">Total Finalized</td>
        <td>{{ $finalizedCount }}</td>
    </tr>

</table>

{{-- V. CERTIFICATION --}}

<div class="section-title">
    V. Certification and Acknowledgment
</div>

<div class="certification">

    I hereby certify that this report accurately reflects the
    attendance, agenda proceedings, deliberations, actions taken,
    and recommendations recorded during the meeting of the
    <strong>{{ $committeeName }}</strong> held on
    <strong>{{ $meeting->meeting_date?->format('F d, Y') }}</strong>.

    The foregoing records are based on the official committee
    meeting records maintained in the Legislative Information System.

</div>

{{-- SIGNATURES --}}

<table class="signature-table">

    <tr>

        <td>

            <div class="signature-line">

                <div class="signature-name">
                    {{ $meeting->presiding_officer ?? '________________________' }}
                </div>

                <div class="signature-position">
                    Committee Chairperson
                </div>

            </div>

        </td>

        <td>

            <div class="signature-line">

                <div class="signature-name">
                    {{ $generatedBy?->name ?? '________________________' }}
                </div>

                <div class="signature-position">
                    Committee Secretary
                </div>

            </div>

        </td>

    </tr>

</table>

{{-- FOOTER --}}

<div class="footer">

    <table class="footer-table">

        <tr>

            <td width="70%">
                Official Committee Deliberation Report |
                LIS
            </td>

            <td width="30%" align="right">

                Page <span class="page-number"></span>
                of <span class="page-count"></span>

            </td>

        </tr>

    </table>

</div>

</body>
</html>
