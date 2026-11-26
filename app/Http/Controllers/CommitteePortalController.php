<?php

namespace App\Http\Controllers;

use App\Models\CommitteeReferral;
use App\Models\CommitteeMeeting;
use App\Models\CommitteeMeetingAgendaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommitteePortalController extends Controller
{
    public function dashboard()
    {
        $account = Auth::guard('member')->user();

        abort_unless(
            $account && $account->is_active,
            403,
            'Your member account is inactive.'
        );

        $member = $account->member;

        abort_unless($member && $member->isActive, 403);

        $committeeIds = $member->committeeAssignments()
            ->where('isActive', true)
            ->pluck('standing_comittee_id');

        $committees = $member->committeeAssignments()
            ->with('committee')
            ->where('isActive', true)
            ->get()
            ->pluck('committee')
            ->filter();

                // Active and upcoming meetings for the member's assigned committees
        $meetings = CommitteeMeeting::with([
            'committee',
            'agendaItems.referral.sessionDocument.document',
            'agendaItems.actions.actor',
        ])
            ->whereIn('committee_id', $committeeIds)
            ->whereIn('status', ['scheduled', 'ongoing'])
            ->orderByRaw("CASE WHEN status = 'ongoing' THEN 0 ELSE 1 END")
            ->orderBy('meeting_date')
            ->get();

        // Agenda items currently being deliberated
        $activeAgendaItems = CommitteeMeetingAgendaItem::with([
            'meeting.committee',
            'referral.sessionDocument.document',
            'actions.actor',
        ])
            ->where('status', 'in_progress')
            ->whereHas('meeting', function ($query) use ($committeeIds) {
                $query->whereIn('committee_id', $committeeIds)
                    ->where('status', 'ongoing');
            })
            ->orderByDesc('updated_at')
            ->get();    

        $referrals = CommitteeReferral::with([
            'committee',
            'sessionDocument.document',
        ])
            ->whereIn('committee_id', $committeeIds)
            ->latest()
            ->paginate(10);

        $counts = CommitteeReferral::whereIn(
            'committee_id',
            $committeeIds
        )
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('committee-portal.dashboard', compact(
            'member',
            'committees',
            'referrals',
            'counts',
            'meetings',
            'activeAgendaItems'
        ));
    }


    public function show($referralId)
    {
        $account = Auth::guard('member')->user();

        abort_unless(
            $account && $account->is_active,
            403,
            'Your member account is inactive.'
        );

        $member = $account->member;

        abort_unless($member && $member->isActive, 403);

        $referral = CommitteeReferral::with([
            'committee',
            'sessionDocument.document',
            'meetingReports.meeting.committee',
            'meetingReports.uploader',
        ])->findOrFail($referralId);

        $isAssigned = $member->committeeAssignments()
            ->where('standing_comittee_id', $referral->committee_id)
            ->where('isActive', true)
            ->exists();

        abort_unless(
            $isAssigned,
            403,
            'You are not assigned to this committee.'
        );

        // Retrieve all meeting appearances of this referral,
        // including deliberation history.
        $agendaItems = CommitteeMeetingAgendaItem::with([
            'meeting.committee',
            'actions' => function ($query) {
                $query->with('actor')
                    ->orderBy('acted_at');
            },
        ])
            ->where('committee_referral_id', $referral->id)
            ->orderByDesc('created_at')
            ->get();

        return view('committee-portal.referrals.show', compact(
            'member',
            'referral',
            'agendaItems'
        ));
    }

    public function startReview($referralId)
    {
        $account = Auth::guard('member')->user();

        abort_unless($account && $account->is_active, 403);

        $member = $account->member;

        abort_unless($member && $member->isActive, 403);

        $referral = CommitteeReferral::findOrFail($referralId);

        $isAssigned = $member->committeeAssignments()
            ->where('standing_comittee_id', $referral->committee_id)
            ->where('isActive', true)
            ->exists();

        abort_unless($isAssigned, 403);

        if ($referral->status !== 'referred') {
            return back()->with('error', 'This referral is not available for review.');
        }

        $referral->update([
            'status' => 'under_review',
        ]);

        return redirect()
            ->route('committee-portal.referrals.show', $referral->id)
            ->with('success', 'Committee review has started.');
    }


    public function submitReport(Request $request, $referralId)
    {
        $account = Auth::guard('member')->user();

        abort_unless($account && $account->is_active, 403);

        $member = $account->member;

        abort_unless($member && $member->isActive, 403);

        $referral = CommitteeReferral::findOrFail($referralId);

        $isAssigned = $member->committeeAssignments()
            ->where('standing_comittee_id', $referral->committee_id)
            ->where('isActive', true)
            ->exists();

        abort_unless($isAssigned, 403);

        if ($referral->status !== 'under_review') {
            return back()->with(
                'error',
                'Only referrals under review can accept a committee report.'
            );
        }

        $validated = $request->validate([
            'recommendation' => ['required', 'string', 'min:3', 'max:10000'],
            'report' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $path = $request->file('report')->store('committee-reports');

        if (! $path) {
            return back()->with(
                'error',
                'The report could not be saved. Please try again.'
            );
        }

        $oldPath = $referral->report_file_path;

        $referral->update([
            'recommendation' => $validated['recommendation'],
            'report_file_path' => $path,
            'report_submitted_at' => now(),
            'status' => 'report_submitted',
        ]);

        if ($oldPath && Storage::exists($oldPath)) {
            Storage::delete($oldPath);
        }

        return redirect()
            ->route('committee-portal.referrals.show', $referral->id)
            ->with('success', 'Committee report submitted successfully.');
    }

    public function downloadMeetingReport($referralId, $reportId)
    {
        $account = Auth::guard('member')->user();

        abort_unless($account && $account->is_active, 403);

        $member = $account->member;

        abort_unless($member && $member->isActive, 403);

        $referral = CommitteeReferral::findOrFail($referralId);

        // Verify that the member is assigned to this referral's committee.
        $isAssigned = $member->committeeAssignments()
            ->where('standing_comittee_id', $referral->committee_id)
            ->where('isActive', true)
            ->exists();

        abort_unless(
            $isAssigned,
            403,
            'You are not assigned to this committee.'
        );

        // Verify that this report is actually linked to this referral.
        $report = $referral->meetingReports()
            ->with('meeting')
            ->where('committee_meeting_reports.id', $reportId)
            ->firstOrFail();

        abort_unless(
            Storage::disk('local')->exists($report->file_path),
            404,
            'Consolidated committee report not found.'
        );

        return Storage::disk('local')->download(
            $report->file_path,
            $report->original_filename ?: 'consolidated-committee-report.pdf'
        );
    }


}