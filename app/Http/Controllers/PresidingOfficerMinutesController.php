<?php

namespace App\Http\Controllers;

use App\Models\SessionMeeting;
use App\Models\SessionMinuteReview;
use App\Models\SessionVote;
use App\Models\SessionVoteEligibility;
use App\Models\SessionVoteRecord;
use App\Models\SessionPreviousMinutesAgendaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresidingOfficerMinutesController extends Controller
{
    public function minutesdashboard()
    {
        // Minutes awaiting review
        $pendingSessions = SessionMeeting::where(
            'minutes_status',
            'for_review'
        )
            ->orderBy('minutes_submitted_at')
            ->paginate(15);

        $pendingReviewCount = SessionMeeting::where(
            'minutes_status',
            'for_review'
        )->count();

        // Minutes returned for corrections
        $revisionSessions = SessionMeeting::where(
            'minutes_status',
            'revision_required'
        )
            ->orderByDesc('updated_at')
            ->paginate(10, ['*'], 'revisions_page');

        $returnedMinutesCount = SessionMeeting::where(
            'minutes_status',
            'revision_required'
        )->count();

        // Previous Minutes agenda items
        $previousMinutesItems = SessionPreviousMinutesAgendaItem::with([
            'session',
            'previousSession',
            'decisionRecordedBy',
        ])
            ->orderByRaw('decision_recorded_at IS NULL DESC')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'previous_minutes_page');

        $previousMinutesPendingCount =
            SessionPreviousMinutesAgendaItem::whereNull(
                'decision_recorded_at'
            )->count();

        $previousMinutesRecordedCount =
            SessionPreviousMinutesAgendaItem::whereNotNull(
                'decision_recorded_at'
            )->count();

        $previousMinutesApprovedCount =
            SessionPreviousMinutesAgendaItem::where(
                'decision',
                'approved'
            )->count();

        $previousMinutesCorrectionsCount =
            SessionPreviousMinutesAgendaItem::where(
                'decision',
                'approved_with_corrections'
            )->count();

        $previousMinutesDeferredCount =
            SessionPreviousMinutesAgendaItem::where(
                'decision',
                'deferred'
            )->count();

        // Voting summary
        $votes = SessionVote::query()
            ->latest()
            ->get();

        $votingStats = [
            'open' => $votes->where('status', 'open')->count(),
            'closed' => $votes->where('status', 'closed')->count(),
            'finalized' => $votes->where('status', 'finalized')->count(),
        ];

        // Recent voting activity
        $recentVotes = $votes->take(5)->map(function ($vote) {
            $vote->eligible_count = SessionVoteEligibility::where(
                'session_vote_id',
                $vote->id
            )
                ->where('eligible', true)
                ->count();

            $vote->ballot_count = SessionVoteRecord::where(
                'session_vote_id',
                $vote->id
            )->count();

            $vote->yes_count = SessionVoteRecord::where(
                'session_vote_id',
                $vote->id
            )
                ->where('vote', 'yes')
                ->count();

            $vote->no_count = SessionVoteRecord::where(
                'session_vote_id',
                $vote->id
            )
                ->where('vote', 'no')
                ->count();

            $vote->abstain_count = SessionVoteRecord::where(
                'session_vote_id',
                $vote->id
            )
                ->where('vote', 'abstain')
                ->count();

            return $vote;
        });

        return view(
            'presiding-officer.minutes-dashboard',
            compact(
                'pendingSessions',
                'revisionSessions',
                'previousMinutesItems',
                'pendingReviewCount',
                'returnedMinutesCount',
                'previousMinutesPendingCount',
                'previousMinutesRecordedCount',
                'previousMinutesApprovedCount',
                'previousMinutesCorrectionsCount',
                'previousMinutesDeferredCount',
                'votingStats',
                'recentVotes'
            )
        );
    }

    public function index()
    {
        $pendingSessions = SessionMeeting::where(
            'minutes_status',
            'for_review'
        )
            ->orderBy('minutes_submitted_at')
            ->paginate(15);

        $revisionSessions = SessionMeeting::where(
            'minutes_status',
            'revision_required'
        )
            ->orderByDesc('updated_at')
            ->paginate(10, ['*'], 'revisions_page');

        // Previous Minutes agenda items for the dashboard
        $previousMinutesItems = SessionPreviousMinutesAgendaItem::with([
            'session',
            'previousSession',
            'decisionRecordedBy',
        ])
            ->orderByRaw('decision_recorded_at IS NULL DESC')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'previous_minutes_page');

        $previousMinutesPendingCount = SessionPreviousMinutesAgendaItem::whereNull(
            'decision_recorded_at'
        )->count();

        return view(
            'presiding-officer.minutes-review',
            compact(
                'pendingSessions',
                'revisionSessions',
                'previousMinutesItems',
                'previousMinutesPendingCount'
            )
        );
    }

    public function markReviewed(Request $request, $id)
    {
        $validated = $request->validate([
            'comments' => ['nullable', 'string', 'max:5000'],
        ]);

        $session = SessionMeeting::findOrFail($id);

        if ($session->minutes_status !== 'for_review') {
            return back()->with(
                'error',
                'Only submitted minutes can be marked as reviewed.'
            );
        }

        DB::transaction(function () use ($request, $session, $validated) {
            $session->update([
                'minutes_status' => 'reviewed',
                'minutes_reviewed_by' => $request->user()->id,
                'minutes_reviewed_at' => now(),
            ]);

            SessionMinuteReview::create([
                'session_id' => $session->id,
                'reviewer_id' => $request->user()->id,
                'action' => 'marked_reviewed',
                'comments' => $validated['comments'] ?? null,
            ]);
        });

        return redirect()
            ->route('presiding-officer.minutes.index')
            ->with('success', 'Minutes marked as reviewed.');
    }

    public function returnForCorrections(Request $request, $id)
    {
        $validated = $request->validate([
            'comments' => ['required', 'string', 'min:3', 'max:5000'],
        ]);

        $session = SessionMeeting::findOrFail($id);

        if ($session->minutes_status !== 'for_review') {
            return back()->with(
                'error',
                'Only submitted minutes can be returned for corrections.'
            );
        }

        DB::transaction(function () use ($request, $session, $validated) {
            $session->update([
                'minutes_status' => 'revision_required',
                'minutes_reviewed_by' => $request->user()->id,
                'minutes_reviewed_at' => now(),
            ]);

            SessionMinuteReview::create([
                'session_id' => $session->id,
                'reviewer_id' => $request->user()->id,
                'action' => 'returned_for_corrections',
                'comments' => $validated['comments'],
            ]);
        });

        return redirect()
            ->route('presiding-officer.minutes.index')
            ->with('success', 'Minutes returned to the Secretary for corrections.');
    }
}
