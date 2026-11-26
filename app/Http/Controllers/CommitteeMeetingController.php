<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\CommitteeMeeting;
use App\Models\CommitteeMeetingAttendance;
use App\Models\CommitteeMeetingAgendaItem;
use App\Models\CommitteeMeetingAgendaAction;
use App\Models\CommitteeReferral;
use App\Models\CommitteeMeetingReport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CommitteeMeetingController extends Controller
{
    public function index()
    {
        $meetings = CommitteeMeeting::with('committee')
            ->orderByDesc('meeting_date')
            ->orderByDesc('start_time')
            ->paginate(15);

        return view('committee-meetings.index', compact('meetings'));
    }

    public function create()
    {
        $committees = Committee::where('isActive', true)
            ->orderBy('name')
            ->get();

        return view('committee-meetings.create', compact('committees'));
    }

    public function show($id)
    {
        $meeting = CommitteeMeeting::with([
            'committee',
            'attendances',
            'agendaItems.referral.sessionDocument.document',
            'agendaItems.actions.actor',
            'report.uploader',
            'report.referrals',
        ])->findOrFail($id);

        $attendanceCounts = [
            'present' => $meeting->attendances->where('attendance_status', 'present')->count(),
            'late' => $meeting->attendances->where('attendance_status', 'late')->count(),
            'absent' => $meeting->attendances->where('attendance_status', 'absent')->count(),
            'excused' => $meeting->attendances->where('attendance_status', 'excused')->count(),
        ];

        $attendanceCounts['total'] = array_sum($attendanceCounts);

        $attendanceCounts['counted_for_quorum'] =
            $attendanceCounts['present'] + $attendanceCounts['late'];

        $requiredQuorum = $meeting->quorum_required;

        $quorumMet = $requiredQuorum !== null
            && $requiredQuorum > 0
            && $attendanceCounts['counted_for_quorum'] >= $requiredQuorum;

        return view('committee-meetings.show', compact('meeting', 'attendanceCounts', 'requiredQuorum', 'quorumMet'));
    }

    public function attendance($id)
    {
        $meeting = CommitteeMeeting::with([
            'committee',
            'attendances',
        ])->findOrFail($id);

        $committeeMembers = $meeting->committee
            ->activeMembers()
            ->with('member')
            ->whereNotNull('member_id')
            ->whereHas('member', function ($query) {
                $query->where('isActive', true);
            })
            ->get();

        return view('committee-meetings.attendance', compact(
            'meeting',
            'committeeMembers'
        ));
    }

    public function manageAgenda($id)
    {
        $meeting = CommitteeMeeting::with([
            'committee',
            'agendaItems.referral.sessionDocument.document',
            'agendaItems.actions.actor',
        ])->findOrFail($id);

        $existingReferralIds = $meeting->agendaItems
            ->pluck('committee_referral_id');

        $availableReferrals = CommitteeReferral::with([
            'sessionDocument.document',
        ])
            ->where('committee_id', $meeting->committee_id)
            ->whereIn('status', [
                'referred',
                'under_review',
                'report_submitted',
            ])
            ->whereNotIn('id', $existingReferralIds)
            ->latest()
            ->get();

        return view('committee-meetings.agenda', compact(
            'meeting',
            'availableReferrals'
        ));
    }

    public function addAgendaItem(Request $request, $id)
    {
        $meeting = CommitteeMeeting::findOrFail($id);

        if (in_array($meeting->status, ['completed', 'cancelled'])) {
            return back()->withErrors([
                'agenda' => 'Agenda cannot be modified for a completed or cancelled meeting.',
            ]);
        }

        $validated = $request->validate([
            'committee_referral_id' => [
                'required',
                'integer',
                'exists:committee_referrals,id',
            ],
        ]);

        $referral = CommitteeReferral::where('id', $validated['committee_referral_id'])
            ->where('committee_id', $meeting->committee_id)
            ->whereIn('status', [
                'referred',
                'under_review',
                'report_submitted',
            ])
            ->first();

        if (!$referral) {
            return back()->withErrors([
                'committee_referral_id' => 'The selected referral is not available for this committee meeting.',
            ]);
        }

        if (
            $meeting->agendaItems()
                ->where('committee_referral_id', $referral->id)
                ->exists()
        ) {
            return back()->withErrors([
                'committee_referral_id' => 'This referral is already included in the meeting agenda.',
            ]);
        }

        DB::transaction(function () use ($meeting, $referral) {
            $lastOrder = $meeting->agendaItems()
                ->lockForUpdate()
                ->max('agenda_order') ?? 0;

            CommitteeMeetingAgendaItem::create([
                'committee_meeting_id' => $meeting->id,
                'committee_referral_id' => $referral->id,
                'agenda_order' => $lastOrder + 1,
                'status' => 'pending',
            ]);
        });

        return redirect()
            ->route('secretary.committee-meetings.agenda', $meeting->id)
            ->with('success', 'Referral successfully added to the meeting agenda.');
    }

    public function reorderAgenda(Request $request, $id)
    {
        $meeting = CommitteeMeeting::findOrFail($id);

        if (in_array($meeting->status, ['completed', 'cancelled'])) {
            return back()->withErrors([
                'agenda' => 'Agenda cannot be modified for a completed or cancelled meeting.',
            ]);
        }

        $validated = $request->validate([
            'agenda_ids' => ['required', 'array', 'min:1'],
            'agenda_ids.*' => [
                'required',
                'integer',
                'distinct',
            ],
        ]);

        DB::transaction(function () use ($meeting, $validated) {
            $items = $meeting->agendaItems()
                ->lockForUpdate()
                ->get();

            $submittedIds = collect($validated['agenda_ids'])
                ->map(fn ($id) => (int) $id);

            $existingIds = $items->pluck('id')
                ->sort()
                ->values();

            if (
                $submittedIds->count() !== $existingIds->count() ||
                $submittedIds->sort()->values()->all() !== $existingIds->all()
            ) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'agenda_ids' => 'The submitted agenda sequence is invalid.',
                ]);
            }

            $offset = ($items->max('agenda_order') ?? 0)
                + $items->count() + 1;

            foreach ($items as $item) {
                $item->update([
                    'agenda_order' => $item->agenda_order + $offset,
                ]);
            }

            foreach ($submittedIds as $index => $itemId) {
                CommitteeMeetingAgendaItem::where('committee_meeting_id', $meeting->id)
                    ->where('id', $itemId)
                    ->update([
                        'agenda_order' => $index + 1,
                    ]);
            }
        });

        return back()->with('success', 'Meeting agenda sequence updated successfully.');
    }

    public function removeAgendaItem($id, $itemId)
    {
        $meeting = CommitteeMeeting::findOrFail($id);

        if (in_array($meeting->status, ['completed', 'cancelled'])) {
            return back()->withErrors([
                'agenda' => 'Agenda cannot be modified for a completed or cancelled meeting.',
            ]);
        }

        $item = $meeting->agendaItems()
            ->with('actions')
            ->findOrFail($itemId);

        if ($item->actions->isNotEmpty() || $item->status !== 'pending') {
            return back()->withErrors([
                'agenda' => 'This agenda item cannot be removed because deliberation has already started or been recorded.',
            ]);
        }

        DB::transaction(function () use ($meeting, $item) {
            $item->delete();

            $remainingItems = $meeting->agendaItems()
                ->orderBy('agenda_order')
                ->lockForUpdate()
                ->get();

            foreach ($remainingItems as $index => $remainingItem) {
                $remainingItem->update([
                    'agenda_order' => $index + 1,
                ]);
            }
        });

        return back()->with('success', 'Agenda item removed successfully.');
    }

    public function saveAttendance(Request $request, $id)
    {
        $meeting = CommitteeMeeting::with('committee')
            ->findOrFail($id);

        if (in_array($meeting->status, ['completed', 'cancelled'])) {
            return back()->withErrors([
                'attendance' => 'Attendance cannot be modified for a completed or cancelled meeting.',
            ]);
        }

        $validated = $request->validate([
            'attendance' => ['required', 'array', 'min:1'],
            'attendance.*.attendance_status' => [
                'required',
                Rule::in(['present', 'absent', 'late', 'excused']),
            ],
            'attendance.*.remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $assignments = $meeting->committee
            ->activeMembers()
            ->with('member')
            ->whereNotNull('member_id')
            ->whereHas('member', function ($query) {
                $query->where('isActive', true);
            })
            ->get()
            ->keyBy('id');

        $submittedIds = collect(array_keys($validated['attendance']));

        if ($submittedIds->diff($assignments->keys())->isNotEmpty()) {
            return back()->withErrors([
                'attendance' => 'Invalid committee member assignment submitted.',
            ]);
        }

        DB::transaction(function () use ($meeting, $validated, $assignments) {
        // Lock the meeting row first. Official deliberation actions use
        // the same lock, preventing concurrent attendance/action updates.
        $lockedMeeting = CommitteeMeeting::whereKey($meeting->id)
            ->lockForUpdate()
            ->firstOrFail();

        if (in_array($lockedMeeting->status, ['completed', 'cancelled'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'attendance' => 'Attendance cannot be modified for a completed or cancelled meeting.',
            ]);
        }    

        foreach ($validated['attendance'] as $assignmentId => $data) {
                $assignment = $assignments->get($assignmentId);

                CommitteeMeetingAttendance::updateOrCreate(
                    [
                        'committee_meeting_id' => $lockedMeeting->id,
                        'committee_member_id' => $assignment->id,
                    ],
                    [
                        'attendee_name' => $assignment->member->name,
                        'attendee_role' => $assignment->position ?? 'Member',
                        'attendance_status' => $data['attendance_status'],
                        'remarks' => $data['remarks'] ?? null,
                    ]
                );
            }
        });

        return redirect()
            ->route('secretary.committee-meetings.attendance', $meeting->id)
            ->with('success', 'Meeting attendance saved successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'committee_id' => [
                'required',
                'integer',
                'exists:standing_comittee,id',
            ],
            'title' => ['required', 'string', 'max:255'],
            'meeting_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'presiding_officer' => ['nullable', 'string', 'max:150'],
            'quorum_required' => ['nullable', 'integer', 'min:1'],
        ]);

        $committee = Committee::findOrFail($validated['committee_id']);

        if (!$committee->isActive) {
            return back()
                ->withInput()
                ->withErrors([
                    'committee_id' => 'The selected committee is inactive.',
                ]);
        }

        if (
            !empty($validated['start_time']) &&
            !empty($validated['end_time']) &&
            $validated['end_time'] <= $validated['start_time']
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'end_time' => 'The meeting end time must be later than the start time.',
                ]);
        }

        $validated['status'] = 'scheduled';

        $meeting = CommitteeMeeting::create($validated);

        return redirect()
            ->route('secretary.committee-meetings.index')
            ->with('success', 'Committee meeting scheduled successfully.');
    }

    public function storeDeliberation(Request $request, $id, $itemId)
    {
        $validated = $request->validate([
            'action' => [
                'required',
                'string',
                Rule::in([
                    'approved',
                    'approved_with_amendments',
                    'returned_for_revision',
                    'deferred',
                    'not_recommended',
                    'for_further_study',
                ]),
            ],
            'reason' => ['nullable', 'string', 'max:10000'],
            'recommendation' => ['nullable', 'string', 'max:10000'],
        ]);

        DB::transaction(function () use ($id, $itemId, $validated) {
            $meeting = CommitteeMeeting::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($meeting->status !== 'ongoing') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'deliberation' => 'The meeting must be ongoing before discussion notes can be recorded.',
                ]);
            }

            $this->ensureQuorum($meeting);

            $agendaItem = $meeting->agendaItems()
                ->lockForUpdate()
                ->findOrFail($itemId);

            if ($agendaItem->status !== 'in_progress') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'deliberation' => 'Start deliberation before recording discussion notes.',
                ]);
            }

            // Ensure this is the current agenda item under deliberation.
            if ((int) $meeting->current_agenda_item_id !== (int) $agendaItem->id) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'deliberation' => 'This agenda item is not the current item under deliberation.',
                ]);
            }

            CommitteeMeetingAgendaAction::create([
                'committee_meeting_id' => $meeting->id,
                'committee_meeting_agenda_item_id' => $agendaItem->id,
                'action' => $validated['action'],
                'reason' => $validated['reason'] ?? null,
                'recommendation' => $validated['recommendation'] ?? null,
                'acted_by' => auth()->id(),
                'acted_at' => now(),
            ]);
        });

        return redirect()
            ->route('secretary.committee-meetings.agenda', $id)
            ->with('success', 'Committee deliberation recorded successfully.');
    }

    public function startDeliberation($id, $itemId)
    {
        DB::transaction(function () use ($id, $itemId) {
            $meeting = CommitteeMeeting::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($meeting->status, ['completed', 'cancelled'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'deliberation' => 'This meeting is already closed.',
                ]);
            }

            if ($meeting->status !== 'ongoing') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'deliberation' => 'Open the committee meeting before starting deliberation.',
                ]);
            }

            // Recalculate quorum from the latest attendance records.
            $this->ensureQuorum($meeting);

            // Prevent another agenda item from starting while one is in progress.
            if ($meeting->current_agenda_item_id !== null) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'deliberation' => 'Another agenda item is currently under deliberation. Finalize it before starting another item.',
                ]);
            }

            $item = $meeting->agendaItems()
                ->lockForUpdate()
                ->findOrFail($itemId);

            if ($item->status !== 'pending') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'deliberation' => 'Only pending agenda items can start deliberation.',
                ]);
            }

            $item->update([
                'status' => 'in_progress',
            ]);

            $referral = $item->referral()
                ->lockForUpdate()
                ->first();

            if ($referral && $referral->status === 'referred') {
                $referral->update([
                    'status' => 'under_review',
                ]);
            }

            $meeting->update([
                'current_agenda_item_id' => $item->id,
            ]);
        });

        return back()->with(
            'success',
            'Deliberation started for this agenda item.'
        );
    }

    public function finalizeDeliberation(Request $request, $id, $itemId)
    {
        $validated = $request->validate([
            'action' => [
                'required',
                'string',
                Rule::in([
                    'approved',
                    'approved_with_amendments',
                    'returned_for_revision',
                    'deferred',
                    'not_recommended',
                    'for_further_study',
                ]),
            ],
            'reason' => ['nullable', 'string', 'max:10000'],
            'recommendation' => ['nullable', 'string', 'max:10000'],
        ]);

        DB::transaction(function () use ($id, $itemId, $validated) {
            $meeting = CommitteeMeeting::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($meeting->status !== 'ongoing') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'deliberation' => 'The meeting must be ongoing before an agenda item can be finalized.',
                ]);
            }

            $this->ensureQuorum($meeting);

            $item = $meeting->agendaItems()
                ->lockForUpdate()
                ->findOrFail($itemId);

            if ($item->status !== 'in_progress') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'deliberation' => 'Only agenda items currently under deliberation can be finalized.',
                ]);
            }

            // Ensure this is the current agenda item under deliberation.
            if ((int) $meeting->current_agenda_item_id !== (int) $item->id) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'deliberation' => 'This agenda item is not the current item under deliberation.',
                ]);
            }


            $statusMap = [
                'approved' => 'completed',
                'approved_with_amendments' => 'completed',
                'not_recommended' => 'completed',
                'deferred' => 'deferred',
                'returned_for_revision' => 'returned_for_revision',
                'for_further_study' => 'for_further_study',
            ];

            CommitteeMeetingAgendaAction::create([
                'committee_meeting_id' => $meeting->id,
                'committee_meeting_agenda_item_id' => $item->id,
                'action' => $validated['action'],
                'reason' => $validated['reason'] ?? null,
                'recommendation' => $validated['recommendation'] ?? null,
                'acted_by' => auth()->id(),
                'acted_at' => now(),
            ]);

            $item->update([
                'status' => $statusMap[$validated['action']],
            ]);

            $meeting->update([
                'current_agenda_item_id' => null,
            ]);
        });

        return redirect()
            ->route('secretary.committee-meetings.agenda', $id)
            ->with('success', 'Committee deliberation finalized successfully.');
    }

    public function closeMeeting($id)
    {
        DB::transaction(function () use ($id) {
            $meeting = CommitteeMeeting::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($meeting->status === 'completed') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'meeting' => 'This meeting has already been completed.',
                ]);
            }

            if ($meeting->status === 'cancelled') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'meeting' => 'A cancelled meeting cannot be closed.',
                ]);
            }

            if ($meeting->status !== 'ongoing') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'meeting' => 'Only an ongoing meeting can be closed.',
                ]);
            }

            $this->ensureQuorum($meeting);

            $agendaItems = $meeting->agendaItems()
                ->lockForUpdate()
                ->get();

            if ($agendaItems->isEmpty()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'meeting' => 'Cannot close a meeting without agenda items.',
                ]);
            }

            $unfinishedItems = $agendaItems->whereNotIn('status', [
                'completed',
                'deferred',
                'returned_for_revision',
                'for_further_study',
            ]);

            if ($unfinishedItems->isNotEmpty()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'meeting' => 'All agenda items must have a final disposition before closing the meeting.',
                ]);
            }

            $meeting->update([
                'status' => 'completed',
                'ended_at' => now(),
                'end_time' => now()->format('H:i:s'),
                'current_agenda_item_id' => null,
            ]);
        });

        return redirect()
            ->route('secretary.committee-meetings.show', $id)
            ->with('success', 'Committee meeting successfully closed.');
    }

    private function ensureQuorum(CommitteeMeeting $meeting): void
    {
        $presentCount = $meeting->attendances()
            ->whereIn('attendance_status', ['present', 'late'])
            ->lockForUpdate()
            ->get(['id'])
            ->count();

        $requiredQuorum = (int) $meeting->quorum_required;

        if (
            $requiredQuorum <= 0 ||
            $presentCount < $requiredQuorum
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quorum' => sprintf(
                    'Official deliberation is locked. Required quorum: %d; current attendance counted for quorum: %d. Update attendance before proceeding.',
                    $requiredQuorum,
                    $presentCount
                ),
            ]);
        }
    }

    public function startMeeting($id)
    {
        DB::transaction(function () use ($id) {
            $meeting = CommitteeMeeting::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($meeting->status === 'completed') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'meeting' => 'This meeting has already been completed.',
                ]);
            }

            if ($meeting->status === 'cancelled') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'meeting' => 'A cancelled meeting cannot be started.',
                ]);
            }

            if ($meeting->status === 'ongoing') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'meeting' => 'This meeting is already ongoing.',
                ]);
            }

            $meeting->update([
                'status' => 'ongoing',
                'started_at' => now(),
            ]);
        });

        return back()->with(
            'success',
            'Committee meeting opened. You may now record attendance and check quorum.'
        );
    }

    public function downloadDeliberationReport($id)
    {
        $meeting = CommitteeMeeting::with([
            'committee',
            'attendances',
            'agendaItems.referral.sessionDocument.document',
            'agendaItems.actions.actor',
        ])->findOrFail($id);

        // Only completed meetings can generate an official report.
        if ($meeting->status !== 'completed') {
            return redirect()
                ->route('secretary.committee-meetings.show', $meeting->id)
                ->withErrors([
                    'report' => 'The deliberation report can only be generated after the meeting is completed.',
                ]);
        }

        $attendanceCounts = [
            'present' => $meeting->attendances->where('attendance_status', 'present')->count(),
            'late' => $meeting->attendances->where('attendance_status', 'late')->count(),
            'absent' => $meeting->attendances->where('attendance_status', 'absent')->count(),
            'excused' => $meeting->attendances->where('attendance_status', 'excused')->count(),
        ];

        $attendanceCounts['total'] = array_sum($attendanceCounts);
        $attendanceCounts['counted_for_quorum'] =
            $attendanceCounts['present'] + $attendanceCounts['late'];

        $requiredQuorum = $meeting->quorum_required;

        $quorumMet = $requiredQuorum !== null
            && $requiredQuorum > 0
            && $attendanceCounts['counted_for_quorum'] >= $requiredQuorum;

        $pdf = Pdf::loadView(
            'committee-meetings.reports.deliberation',
            [
                'meeting' => $meeting,
                'attendanceCounts' => $attendanceCounts,
                'requiredQuorum' => $requiredQuorum,
                'quorumMet' => $quorumMet,
                'generatedBy' => auth()->user(),
            ]
        )->setPaper('a4', 'portrait');

        $filename = 'committee-deliberation-report-' . $meeting->id . '.pdf';

        $canvas = $pdf->getDomPDF()->getCanvas();
        $font = $pdf->getDomPDF()
            ->getFontMetrics()
            ->getFont('Arial', 'normal');

        $canvas->page_text(
            $canvas->get_width() - 105,
            $canvas->get_height() - 28,
            'Page {PAGE_NUM} of {PAGE_COUNT}',
            $font,
            8,
            [0.4, 0.4, 0.4]
        );

        return $pdf->download($filename);
    }

    
public function uploadMeetingReport(Request $request, $id)
{
    $request->validate([
        'report' => [
            'required',
            'file',
            'mimes:pdf',
            'max:20480',
        ],
    ]);

    $newPath = null;
    $oldPath = null;

    try {
        DB::transaction(function () use (
            $request,
            $id,
            &$newPath,
            &$oldPath
        ) {
            $meeting = CommitteeMeeting::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($meeting->status !== 'completed') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'report' => 'The consolidated report can only be uploaded after the meeting is completed.',
                ]);
            }

            $agendaItems = $meeting->agendaItems()
                ->whereNotNull('committee_referral_id')
                ->lockForUpdate()
                ->get();

            if ($agendaItems->isEmpty()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'report' => 'This meeting has no agenda referrals.',
                ]);
            }

            // Collect final recommendations from completed agenda items.
            $finalRecommendations = [];

            foreach ($agendaItems as $agendaItem) {
                // Only completed deliberations become report_submitted.
                // Deferred and other dispositions retain their own workflow.
                if ($agendaItem->status !== 'completed') {
                    continue;
                }

                // Get the final deliberation action, not an earlier discussion note.
                $finalAction = $agendaItem->actions()
                    ->whereIn('action', [
                        'approved',
                        'approved_with_amendments',
                        'not_recommended',
                    ])
                    ->orderByDesc('acted_at')
                    ->orderByDesc('id')
                    ->first();

                if (
                    !$finalAction ||
                    blank($finalAction->recommendation)
                ) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'report' => 'Agenda item #' . $agendaItem->agenda_order .
                            ' is completed but has no final recommendation. Please verify its deliberation record before uploading.',
                    ]);
                }

                $finalRecommendations[$agendaItem->committee_referral_id] =
                    $finalAction->recommendation;
            }

            if (empty($finalRecommendations)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'report' => 'No completed agenda items with final recommendations were found.',
                ]);
            }

            $existingReport = CommitteeMeetingReport::where(
                'committee_meeting_id',
                $meeting->id
            )->lockForUpdate()->first();

            $newPath = $request->file('report')->store(
                'committee-meeting-reports',
                'local'
            );

            if (!$newPath) {
                throw new \RuntimeException(
                    'The consolidated report could not be stored.'
                );
            }

            $oldPath = $existingReport?->file_path;

            $report = CommitteeMeetingReport::updateOrCreate(
                [
                    'committee_meeting_id' => $meeting->id,
                ],
                [
                    'file_path' => $newPath,
                    'original_filename' => $request->file('report')
                        ->getClientOriginalName(),
                    'uploaded_by' => auth()->id(),
                    'uploaded_at' => now(),
                ]
            );

            // Link the single meeting PDF to every referral on the agenda.
            $referralIds = $agendaItems
                ->pluck('committee_referral_id')
                ->unique()
                ->values();

            $report->referrals()->sync($referralIds);

            // Update each completed referral using its own final recommendation.
            foreach ($finalRecommendations as $referralId => $recommendation) {
                $referral = CommitteeReferral::whereKey($referralId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $updates = [
                    'recommendation' => $recommendation,
                    'report_submitted_at' =>
                        $referral->report_submitted_at ?? now(),
                ];

                // Do not regress referrals already returned or closed.
                if (in_array($referral->status, [
                    'referred',
                    'under_review',
                    'report_submitted',
                ], true)) {
                    $updates['status'] = 'report_submitted';
                }

                $referral->update($updates);
            }
        });
    } catch (\Throwable $exception) {
        if ($newPath) {
            Storage::disk('local')->delete($newPath);
        }

        throw $exception;
    }

    if ($oldPath && $oldPath !== $newPath) {
        Storage::disk('local')->delete($oldPath);
    }

    return redirect()
        ->route('secretary.committee-meetings.show', $id)
        ->with(
            'success',
            'Consolidated report uploaded. Final recommendations have been recorded and completed referrals have been marked as report submitted.'
        );
}



    public function downloadMeetingReport($id)
    {
        $meeting = CommitteeMeeting::with('report')
            ->findOrFail($id);

        if (!$meeting->report) {
            abort(404, 'No consolidated committee report has been uploaded.');
        }

        $report = $meeting->report;

        if (!Storage::disk('local')->exists($report->file_path)) {
            abort(404, 'The consolidated committee report file could not be found.');
        }

        return Storage::disk('local')->download(
            $report->file_path,
            $report->original_filename ?: 'consolidated-committee-report.pdf'
        );
    }
}