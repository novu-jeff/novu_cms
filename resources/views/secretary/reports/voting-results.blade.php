
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Official Council Voting Results</title>

    <style>
        @page {
            margin: 35px 40px 55px 40px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .header h2 {
            margin: 3px 0;
            font-size: 15px;
            text-transform: uppercase;
        }

        .header h3 {
            margin: 3px 0;
            font-size: 12px;
            font-weight: normal;
        }

        .report-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            margin: 18px 0;
            text-transform: uppercase;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            background: #eeeeee;
            padding: 6px 8px;
            margin-top: 15px;
            margin-bottom: 7px;
            border-left: 3px solid #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            border: 1px solid #bbb;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .no-border td {
            border: none;
            padding: 4px 2px;
        }

        .tally td {
            text-align: center;
            padding: 12px 5px;
        }

        .tally .number {
            font-size: 18px;
            font-weight: bold;
        }

        .center {
            text-align: center;
        }

        .muted {
            color: #666;
        }

        .signature-table {
            margin-top: 35px;
        }

        .signature-table td {
            border: none;
            width: 50%;
            padding: 25px 15px 5px;
            text-align: center;
            vertical-align: bottom;
        }

        .signature-line {
            border-top: 1px solid #222;
            padding-top: 6px;
            margin-top: 35px;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #666;
        }

        .page-number:after {
            content: counter(page);
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>Republic of the Philippines</h2>
        <h3>City Government of Tabuk</h3>
        <h3>Sangguniang Panlungsod</h3>
    </div>

    <div class="report-title">
        Official Council Voting Results
    </div>

    <div class="section-title">I. Council Session Information</div>

    <table class="no-border">
        <tr>
            <td width="25%"><strong>Session Title:</strong></td>
            <td width="75%">{{ $session->title }}</td>
        </tr>
        <tr>
            <td><strong>Session Number:</strong></td>
            <td>{{ $session->session_number ?? '—' }}</td>
        </tr>
        <tr>
            <td><strong>Legislative Term:</strong></td>
            <td>{{ $session->legislative_term ?? '—' }}</td>
        </tr>
        <tr>
            <td><strong>Session Date:</strong></td>
            <td>{{ $session->session_date?->format('F d, Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td><strong>Venue:</strong></td>
            <td>{{ $session->venue ?? '—' }}</td>
        </tr>
        <tr>
            <td><strong>Presiding Officer:</strong></td>
            <td>{{ $session->presiding_officer ?? '—' }}</td>
        </tr>
    </table>

    <div class="section-title">II. Agenda Item and Motion Details</div>

    <table class="no-border">
        <tr>
            <td width="25%"><strong>Agenda Item:</strong></td>
            <td>{{ $agendaTitle }}</td>
        </tr>
        <tr>
            <td><strong>Motion:</strong></td>
            <td>{{ $vote->motion_title }}</td>
        </tr>
        <tr>
            <td><strong>Description:</strong></td>
            <td>{{ $vote->motion_description ?: '—' }}</td>
        </tr>
        <tr>
            <td><strong>Voting Method:</strong></td>
            <td>{{ $vote->voting_method === 'roll_call' ? 'Roll Call' : 'Secret Ballot' }}</td>
        </tr>
        <tr>
            <td><strong>Voting Period:</strong></td>
            <td>
                {{ $vote->opened_at?->format('F d, Y h:i A') ?? '—' }}
                to
                {{ $vote->closed_at?->format('F d, Y h:i A') ?? '—' }}
            </td>
        </tr>
    </table>

    <div class="section-title">III. Final Voting Tally</div>

    <table class="tally">
        <tr>
            <th>YES</th>
            <th>NO</th>
            <th>ABSTAIN</th>
            <th>TOTAL BALLOTS</th>
        </tr>
        <tr>
            <td><div class="number">{{ $yes }}</div></td>
            <td><div class="number">{{ $no }}</div></td>
            <td><div class="number">{{ $abstain }}</div></td>
            <td><div class="number">{{ $totalVotes }}</div></td>
        </tr>
    </table>

    <p>
        <strong>Eligible Councilors:</strong>
        {{ $eligibleMembers->count() }}
        &nbsp;&nbsp;
        <strong>Submitted Ballots:</strong>
        {{ $totalVotes }}
        &nbsp;&nbsp;
        <strong>Pending:</strong>
        {{ $pendingCount }}
    </p>

    <div class="section-title">IV. Attendance and Participation</div>

    <table>
        <thead>
            <tr>
                <th width="7%">No.</th>
                <th>Attendee</th>
                <th>Role</th>
                <th>Attendance Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendance as $index => $attendee)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $attendee->attendee_name }}</td>
                    <td>{{ $attendee->attendee_role ?? '—' }}</td>
                    <td>{{ ucfirst($attendee->attendance_status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="center">No attendance records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p>
        <strong>Total Attendance Records:</strong> {{ $attendanceCount }}
        &nbsp;&nbsp;
        <strong>Present/Late:</strong> {{ $presentCount }}
        &nbsp;&nbsp;
        <strong>Absent:</strong> {{ $absentCount }}
    </p>

    @if($vote->voting_method === 'roll_call')
        <div class="section-title">V. Individual Roll-Call Voting Records</div>

        <table>
            <thead>
                <tr>
                    <th width="7%">No.</th>
                    <th>Councilor</th>
                    <th>Vote</th>
                    <th>Date and Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($eligibleMembers as $index => $member)
                    @php
                        $record = $individualRecords->get($member->id);
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $member->member?->name ?? $member->email }}</td>
                        <td>{{ $record ? strtoupper($record->vote) : 'NOT YET VOTED' }}</td>
                        <td>{{ $record?->cast_at?->format('F d, Y h:i A') ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="section-title">V. Secret Ballot Certification</div>
        <p>
            This voting event was conducted through secret ballot.
            Individual voting choices are not disclosed in this report.
            Only the consolidated voting tally is presented.
        </p>
    @endif

    <div class="section-title">
        {{ $vote->voting_method === 'roll_call' ? 'VI' : 'VI' }}. Certification and Signatures
    </div>

    <p>
        This report reflects the voting records and final tally stored in
        the Council's session management system as of the finalization date
        indicated below.
    </p>

    <p>
        <strong>Finalized On:</strong>
        {{ $vote->finalized_at?->format('F d, Y h:i A') ?? '—' }}
    </p>

    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-line">
                    {{ $session->presiding_officer ?? 'Presiding Officer' }}
                </div>
                Presiding Officer
            </td>
            <td>
                <div class="signature-line">
                    ______________________________
                </div>
                Secretary to the Sanggunian
            </td>
        </tr>
    </table>

    <div class="footer">
        Official Council Voting Results |
        Session {{ $session->session_number ?? $session->id }} |
        Page <span class="page-number"></span>
    </div>

</body>
</html>
