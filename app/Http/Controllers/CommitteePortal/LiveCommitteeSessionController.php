<?php

namespace App\Http\Controllers\CommitteePortal;

use App\Http\Controllers\Controller;
use App\Models\CommitteeMeeting;
use Illuminate\Support\Facades\Auth;

class LiveCommitteeSessionController extends Controller
{
    /**
     * Validate the authenticated member account and retrieve
     * the member's active committee assignments.
     */
    private function memberContext(): array
    {
        $account = Auth::guard('member')->user();

        abort_unless(
            $account && $account->is_active,
            403,
            'Your member account is inactive.'
        );

        $member = $account->member;

        abort_unless(
            $member && $member->isActive,
            403,
            'Your member profile is inactive.'
        );

        $committeeIds = $member->committeeAssignments()
            ->where('isActive', true)
            ->pluck('standing_comittee_id')
            ->filter()
            ->unique()
            ->values();

        return [$member, $committeeIds];
    }

    /**
     * Display meetings belonging to the member's assigned committees.
     */
    public function index()
    {
        [$member, $committeeIds] = $this->memberContext();

        $meetings = CommitteeMeeting::with([
            'committee',
            'attendances',
            'agendaItems.referral.sessionDocument.document',
            'agendaItems.actions.actor',
        ])
            ->whereIn('committee_id', $committeeIds)
            ->whereIn('status', [
                'scheduled',
                'ongoing',
                'completed',
            ])
            ->orderByRaw("
                CASE
                    WHEN status = 'ongoing' THEN 0
                    WHEN status = 'scheduled' THEN 1
                    WHEN status = 'completed' THEN 2
                    ELSE 3
                END
            ")
            ->orderByDesc('meeting_date')
            ->paginate(12);

        return view(
            'committee-portal.meetings.index',
            compact('member', 'meetings')
        );
    }

    /**
     * Display the read-only live meeting workspace.
     */
    public function show($meeting)
    {
        [$member, $committeeIds] = $this->memberContext();

        // Restrict the meeting lookup itself to assigned committees.
        $meeting = CommitteeMeeting::with([
            'committee',
            'attendances.committeeMember',
            'agendaItems.referral.committee',
            'agendaItems.referral.sessionDocument.document',
            'agendaItems.actions.actor',
        ])
            ->whereIn('committee_id', $committeeIds)
            ->findOrFail($meeting);

        $presentCount = $meeting->attendances
            ->whereIn('attendance_status', ['present', 'late'])
            ->count();

        $requiredQuorum = (int) $meeting->quorum_required;

        $quorumReached = $requiredQuorum > 0
            && $presentCount >= $requiredQuorum;

        $currentAgendaItem = $meeting->agendaItems
            ->firstWhere('id', $meeting->current_agenda_item_id);

        return view(
            'committee-portal.meetings.show',
            compact(
                'member',
                'meeting',
                'presentCount',
                'requiredQuorum',
                'quorumReached',
                'currentAgendaItem'
            )
        );
    }

    /**
     * Read-only meeting state endpoint for AJAX polling.
     */
    public function state($meeting)
    {
        [, $committeeIds] = $this->memberContext();

        $meeting = CommitteeMeeting::with([
            'committee',
            'attendances.committeeMember',
            'agendaItems.referral.sessionDocument.document',
            'agendaItems.actions.actor',
        ])
            ->whereIn('committee_id', $committeeIds)
            ->findOrFail($meeting);

        $presentCount = $meeting->attendances
            ->whereIn('attendance_status', ['present', 'late'])
            ->count();

        $requiredQuorum = (int) $meeting->quorum_required;

        $quorumReached = $requiredQuorum > 0
            && $presentCount >= $requiredQuorum;

        return response()->json([
            'success' => true,
            'meeting' => [
                'id' => $meeting->id,
                'title' => $meeting->title,
                'status' => $meeting->status,
                'meeting_date' => $meeting->meeting_date?->format('Y-m-d'),
                'start_time' => $meeting->start_time,
                'end_time' => $meeting->end_time,
                'venue' => $meeting->venue,
                'committee' => $meeting->committee?->name,
                'started_at' => $meeting->started_at?->toDateTimeString(),
                'ended_at' => $meeting->ended_at?->toDateTimeString(),
                'current_agenda_item_id' => $meeting->current_agenda_item_id,
            ],
            'quorum' => [
                'present_count' => $presentCount,
                'required' => $requiredQuorum,
                'reached' => $quorumReached,
                'status' => $quorumReached
                    ? 'reached'
                    : 'waiting',
            ],
            'attendance' => $meeting->attendances->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'name' => $attendance->attendee_name
                        ?: $attendance->committeeMember?->member?->name
                        ?: 'Committee Member',
                    'role' => $attendance->attendee_role,
                    'status' => $attendance->attendance_status,
                    'remarks' => $attendance->remarks,
                ];
            })->values(),
            'agenda_items' => $meeting->agendaItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'agenda_order' => $item->agenda_order,
                    'status' => $item->status,
                    'referral_id' => $item->committee_referral_id,
                    'referral' => $item->referral,
                    'actions' => $item->actions->map(function ($action) {
                        return [
                            'id' => $action->id,
                            'action' => $action->action,
                            'reason' => $action->reason,
                            'recommendation' => $action->recommendation,
                            'acted_at' => $action->acted_at?->toDateTimeString(),
                            'actor' => $action->actor?->name,
                        ];
                    })->values(),
                ];
            })->values(),
            'updated_at' => now()->toDateTimeString(),
        ]);
    }
}
