<?php

namespace App\Http\Controllers;

use App\Models\SessionMeeting;
use App\Models\SessionDocument;
use App\Models\SessionVote;
use App\Models\SessionVoteEligibility;
use App\Models\SessionVoteRecord;
use App\Models\CommitteeReportAgendaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\MembersAccount;
use App\Models\SessionAttendance;
use App\Services\DocumentSigning\DocumentSigningService;
use App\Models\DocumentSigningRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;


class SessionVotingController extends Controller
{
    

   
    public function manage($sessionId)
    {
        $session = SessionMeeting::findOrFail($sessionId);


        $presentCount = SessionAttendance::query()
        ->where('session_id', $session->id)
        ->whereNotNull('member_account_id')
        ->whereIn('attendance_status', ['present', 'late'])
        ->distinct('member_account_id')
        ->count('member_account_id');

        $quorumRequired = (int) ($session->quorum_required ?? 0);

        $quorumMet = $quorumRequired > 0
            ? $presentCount >= $quorumRequired
            : null;


        // Regular documents.
        $documents = SessionDocument::with('document')
            ->where('session_id', $session->id)
            ->orderBy('agenda_order')
            ->get()
            ->map(function ($item) {
                $item->agenda_type = 'document';
                $item->title = $item->document?->title ?? 'Untitled Document';

                return $item;
            });

        // Committee reports.
        $committeeReports = CommitteeReportAgendaItem::with([
            'referral.committee',
            'referral.sessionDocument.document',
        ])
            ->where('session_id', $session->id)
            ->orderBy('agenda_order')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'id' => $item->id,
                    'agenda_type' => 'committee_report',
                    'agenda_order' => $item->agenda_order,
                    'status' => $item->status,
                    'title' => $item->referral?->sessionDocument?->document?->title
                        ?? 'Committee Report',
                    'committee_name' => $item->referral?->committee?->name,
                ];
            });

        // Combine both agenda types.
        $agendaItems = $documents
            ->concat($committeeReports)
            ->sortBy('agenda_order')
            ->values();

        // Resolve the active agenda item using both ID and type.
        $currentType = $session->current_agenda_item_type ?: 'document';

        $activeAgendaItem = $agendaItems->first(function ($item) use (
            $session,
            $currentType
        ) {
            return (int) $item->id === (int) $session->current_agenda_item_id
                && $item->agenda_type === $currentType;
        });

        $presentMemberIds = SessionAttendance::where('session_id', $session->id)
        ->whereNotNull('member_account_id')
        ->whereIn('attendance_status', ['present', 'late'])
        ->distinct()
        ->pluck('member_account_id');

    $members = MembersAccount::with('member')
        ->where('is_active', 1)
        ->whereIn('id', $presentMemberIds)
        ->orderBy('id')
        ->get();

        $votes = SessionVote::where('session_id', $session->id)
            ->latest('id')
            ->get();

        $signingRequests = DocumentSigningRequest::query()
            ->where('document_type', 'voting_results')
            ->whereIn('document_id', $votes->pluck('id'))
            ->latest('id')
            ->get()
            ->groupBy('document_id')
            ->map(fn ($requests) => $requests->first());

          

        foreach ($votes as $vote) {
            $vote->signing_request = $signingRequests->get($vote->id);
            $recordsQuery = SessionVoteRecord::query()
                ->where('session_vote_id', $vote->id);

            $vote->yes_count = (clone $recordsQuery)
                ->where('vote', 'yes')->count();

            $vote->no_count = (clone $recordsQuery)
                ->where('vote', 'no')->count();

            $vote->abstain_count = (clone $recordsQuery)
                ->where('vote', 'abstain')->count();

            $vote->eligible_count = SessionVoteEligibility::where(
                'session_vote_id',
                $vote->id
            )->where('eligible', true)->count();

            $vote->ballot_count = (clone $recordsQuery)->count();

            // Load eligible councilors and their voting status.
            $eligibleIds = SessionVoteEligibility::where(
                'session_vote_id',
                $vote->id
            )
                ->where('eligible', true)
                ->pluck('member_account_id');

            $vote->eligible_members = MembersAccount::with('member')
                ->whereIn('id', $eligibleIds)
                ->orderBy('id')
                ->get();

            // Retrieve individual records only for roll-call votes.
            if ($vote->voting_method === 'roll_call') {
                $vote->individual_records = SessionVoteRecord::with(
                    'memberAccount.member'
                )
                    ->where('session_vote_id', $vote->id)
                    ->get()
                    ->keyBy('member_account_id');
            } else {
                $vote->individual_records = collect();
            }
        }

        return view('secretary.voting-management', compact(
            'session',
            'agendaItems',
            'activeAgendaItem',
            'members',
            'votes',
            'presentCount',
            'quorumRequired',
            'quorumMet',
        ));
    }



    /**
     * Open a voting event for the active agenda item.
     *
     * eligible_member_ids must contain CMS member_account_id values,
     * supplied by trusted server-side eligibility logic.
     */
    public function open(Request $request, $sessionId)
    {
        
        $session = SessionMeeting::findOrFail($sessionId);

        // Quorum must be configured before a vote can be opened.
        $quorumRequired = (int) ($session->quorum_required ?? 0);

        if ($quorumRequired < 1) {
            $message = 'Voting cannot begin because the required quorum has not been configured. Please set the quorum and save attendance first.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        // Count attendees who are present or late.
        $presentCount = SessionAttendance::query()
            ->where('session_id', $session->id)
            ->whereNotNull('member_account_id')
            ->whereIn('attendance_status', ['present', 'late'])
            ->distinct('member_account_id')
            ->count('member_account_id');

        if ($presentCount < $quorumRequired) {
            $message = "Voting cannot begin. Quorum not met: {$presentCount} present or late, {$quorumRequired} required.";

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'present_count' => $presentCount,
                    'quorum_required' => $quorumRequired,
                    'quorum_met' => false,
                ], 422);
            }

            return back()->with('error', $message);
        }

        $data = $request->validate([
            'motion_title' => ['required', 'string', 'max:255'],
            'motion_description' => ['nullable', 'string'],
            'voting_method' => [
                'required',
                Rule::in(['roll_call', 'secret']),
            ],
            'eligible_member_ids' => ['required', 'array', 'min:1'],
            'eligible_member_ids.*' => [
                'required',
                'integer',
                'distinct',
                'min:1',
            ],
        ]);

        $actorId = auth()->id();

        $vote = DB::connection('mysql')->transaction(
            function () use ($sessionId, $data, $actorId) {
                $session = SessionMeeting::query()
                    ->lockForUpdate()
                    ->findOrFail($sessionId);

                if ($session->status !== 'ongoing') {
                    throw ValidationException::withMessages([
                        'session' => 'Voting requires an ongoing session.',
                    ]);
                }

                if (!$session->current_agenda_item_id) {
                    throw ValidationException::withMessages([
                        'agenda' => 'Activate an agenda item before opening a vote.',
                    ]);
                }

                $agendaType = $session->current_agenda_item_type ?: 'document';

                if ($agendaType === 'committee_report') {
                    $agendaItem = CommitteeReportAgendaItem::query()
                        ->where('session_id', $session->id)
                        ->whereKey($session->current_agenda_item_id)
                        ->first();
                } else {
                    $agendaItem = SessionDocument::query()
                        ->where('session_id', $session->id)
                        ->whereKey($session->current_agenda_item_id)
                        ->first();
                }

                if (!$agendaItem || $agendaItem->status !== 'in_progress') {
                    throw ValidationException::withMessages([
                        'agenda' => 'The active agenda item is invalid.',
                    ]);
                }


                $hasOpenVote = SessionVote::query()
                    ->where('session_id', $session->id)
                    ->where('status', 'open')
                    ->exists();

                if ($hasOpenVote) {
                    throw ValidationException::withMessages([
                        'vote' => 'Close the existing open vote first.',
                    ]);
                }

                $vote = SessionVote::create([
                    'session_id' => $session->id,

                    'session_document_id' => $agendaType === 'document'
                        ? $agendaItem->id
                        : null,

                    'committee_report_agenda_item_id' => $agendaType === 'committee_report'
                        ? $agendaItem->id
                        : null,

                    'motion_title' => $data['motion_title'],
                    'motion_description' => $data['motion_description'] ?? null,
                    'voting_method' => $data['voting_method'],
                    'status' => 'open',
                    'opened_by' => $actorId,
                    'opened_at' => now(),
                ]);

                $presentMemberIds = SessionAttendance::query()
                ->where('session_id', $session->id)
                ->whereNotNull('member_account_id')
                ->whereIn('attendance_status', ['present', 'late'])
                ->distinct()
                ->pluck('member_account_id');

            $eligibleIds = MembersAccount::query()
                ->where('is_active', 1)
                ->whereIn('id', $presentMemberIds)
                ->pluck('id');

            if ($eligibleIds->isEmpty()) {
                throw ValidationException::withMessages([
                    'eligible_member_ids' => 'No eligible active councilors are present.',
                ]);
            }

            foreach ($eligibleIds as $memberAccountId) {
                SessionVoteEligibility::create([
                    'session_vote_id' => $vote->id,
                    'member_account_id' => $memberAccountId,
                    'eligible' => true,
                ]);
            }

                return $vote->load('eligibilities');
            }
        );

        
        if (!$request->expectsJson()) {
            return redirect()
                ->route('secretary.votes.manage', $sessionId)
                ->with('success', 'Voting opened successfully.');
        }

        return response()->json([
            'message' => 'Voting opened successfully.',
            'vote' => $vote,
        ], 201);

    }

    /**
     * Cast one ballot.
     *
     * IMPORTANT: resolve the member account ID from the authenticated
     * LIS identity on the server. Never trust an ID supplied by the client.
     */
    public function cast(Request $request, $voteId)
    {
        $data = $request->validate([
            'vote' => [
                'required',
                Rule::in(['yes', 'no', 'abstain']),
            ],
        ]);

        // Replace this with the verified LIS-user-to-CMS-member mapping.
        //$memberAccountId = $this->resolveMemberAccountId($request);

        $account = $request->user();

        if (!$account || !$account->is_active) {
            abort(403, 'An active member account is required to vote.');
        }

        $memberAccountId = (int) $account->id;

        $record = DB::connection('mysql')->transaction(
            function () use ($voteId, $data, $memberAccountId) {
                $vote = SessionVote::query()
                    ->lockForUpdate()
                    ->findOrFail($voteId);

                if ($vote->status !== 'open') {
                    throw ValidationException::withMessages([
                        'vote' => 'This vote is no longer open.',
                    ]);
                }

                $session = SessionMeeting::findOrFail($vote->session_id);

                $voteAgendaType = $vote->committee_report_agenda_item_id
                    ? 'committee_report'
                    : 'document';

                if (
                    $session->status !== 'ongoing' ||
                    (int) $session->current_agenda_item_id !== (int) (
                        $voteAgendaType === 'committee_report'
                            ? $vote->committee_report_agenda_item_id
                            : $vote->session_document_id
                    ) ||
                    ($session->current_agenda_item_type ?: 'document') !== $voteAgendaType
                ) {
                    throw ValidationException::withMessages([
                        'vote' => 'The voting agenda item is no longer active.',
                    ]);
                }

                $eligibility = SessionVoteEligibility::query()
                    ->where('session_vote_id', $vote->id)
                    ->where('member_account_id', $memberAccountId)
                    ->first();

                if (!$eligibility || !$eligibility->eligible) {
                    throw ValidationException::withMessages([
                        'vote' => 'You are not eligible to vote on this motion.',
                    ]);
                }

                $alreadyVoted = SessionVoteRecord::query()
                    ->where('session_vote_id', $vote->id)
                    ->where('member_account_id', $memberAccountId)
                    ->exists();

                if ($alreadyVoted) {
                    throw ValidationException::withMessages([
                        'vote' => 'A ballot has already been recorded for you.',
                    ]);
                }

                return SessionVoteRecord::create([
                    'session_vote_id' => $vote->id,
                    'member_account_id' => $memberAccountId,
                    'vote' => $data['vote'],
                    'cast_at' => now(),
                ]);
            }
        );

        return response()->json([
            'message' => 'Vote recorded successfully.',
            'record_id' => $record->id,
        ], 201);
    }

    /**
     * Close voting. Existing ballots remain unchanged.
     */
    public function close(Request $request, $voteId)
    {
        $actorId = auth()->id();

        $vote = DB::connection('mysql')->transaction(
            function () use ($voteId, $actorId) {
                $vote = SessionVote::query()
                    ->lockForUpdate()
                    ->findOrFail($voteId);

                if ($vote->status !== 'open') {
                    throw ValidationException::withMessages([
                        'vote' => 'Only an open vote can be closed.',
                    ]);
                }

                $session = SessionMeeting::query()
                    ->lockForUpdate()
                    ->findOrFail($vote->session_id);

                $agendaType = $vote->committee_report_agenda_item_id
                    ? 'committee_report'
                    : 'document';

                $agendaItemId = $vote->committee_report_agenda_item_id
                    ?: $vote->session_document_id;

                if (
                    $session->status !== 'ongoing' ||
                    (int) $session->current_agenda_item_id !== (int) $agendaItemId ||
                    ($session->current_agenda_item_type ?: 'document') !== $agendaType
                ) {
                    throw ValidationException::withMessages([
                        'vote' => 'This vote cannot be closed because its agenda item is no longer active.',
                    ]);
                }

                $vote->status = 'closed';
                $vote->closed_by = $actorId;
                $vote->closed_at = now();
                $vote->save();

                return $vote;
            }
        );

        if (!$request->expectsJson()) {
            return redirect()
                ->route('secretary.votes.manage', $vote->session_id)
                ->with('success', 'Voting closed successfully.');
        }

        return response()->json([
            'message' => 'Voting closed successfully.',
            'vote' => $vote,
        ]);
    }

    /**
     * Finalize a closed vote and persist the tally.
     *
     * This records counts, not a policy-defined pass/fail decision.
     */
    public function finalize(Request $request, $voteId)
    {
        $actorId = auth()->id();

        $vote = DB::connection('mysql')->transaction(
            function () use ($voteId, $actorId) {
                $vote = SessionVote::query()
                    ->lockForUpdate()
                    ->findOrFail($voteId);

                $session = SessionMeeting::query()
                    ->lockForUpdate()
                    ->findOrFail($vote->session_id);

                if ($session->status === 'suspended') {
                    throw ValidationException::withMessages([
                        'vote' => 'Voting results cannot be finalized while the session is suspended. Resume the session first.',
                    ]);
                }

                if ($vote->status !== 'closed') {
                    throw ValidationException::withMessages([
                        'vote' => 'Only a closed vote can be finalized.',
                    ]);
                }

                $counts = SessionVoteRecord::query()
                    ->where('session_vote_id', $vote->id)
                    ->selectRaw(
                        "SUM(CASE WHEN vote = 'yes' THEN 1 ELSE 0 END) AS yes_count"
                    )
                    ->selectRaw(
                        "SUM(CASE WHEN vote = 'no' THEN 1 ELSE 0 END) AS no_count"
                    )
                    ->selectRaw(
                        "SUM(CASE WHEN vote = 'abstain' THEN 1 ELSE 0 END) AS abstain_count"
                    )
                    ->first();

                $yes = (int) ($counts->yes_count ?? 0);
                $no = (int) ($counts->no_count ?? 0);
                $abstain = (int) ($counts->abstain_count ?? 0);

                $vote->result = json_encode([
                    'yes' => $yes,
                    'no' => $no,
                    'abstain' => $abstain,
                    'total_votes' => $yes + $no + $abstain,
                ]);

                $vote->status = 'finalized';
                $vote->finalized_by = $actorId;
                $vote->finalized_at = now();
                $vote->save();

                return $vote;
            }
        );

        if (!$request->expectsJson()) {
            return redirect()
                ->route('secretary.votes.manage', $vote->session_id)
                ->with('success', 'Vote finalized successfully.');
        }

        return response()->json([
            'message' => 'Vote finalized successfully.',
            'vote' => $vote,
            'result' => json_decode($vote->result, true),
        ]);
    }

    /**
     * Integration point: map the authenticated LIS identity to the
     * canonical member_account_id stored in the CMS.
     *
     * Do not implement this by assuming auth()->id() is the same
     * as member_account_id across the two databases.
     */
    
    public function activeVote(Request $request)
    {
        $account = $request->user();

        if (!$account || !$account->is_active) {
            return response()->json([
                'message' => 'Your account is inactive.',
            ], 403);
        }

        $vote = SessionVote::query()
            ->where('status', 'open')
            ->whereHas('eligibilities', function ($query) use ($account) {
                $query->where('member_account_id', $account->id)
                    ->where('eligible', true);
            })
            ->whereHas('session', function ($query) {
                $query->where('status', 'ongoing');
            })
            ->latest('id')
            ->first();

        if (!$vote) {
            return response()->json([
                'message' => 'There is no active vote available to you.',
                'vote' => null,
            ]);
        }

        $hasVoted = SessionVoteRecord::query()
            ->where('session_vote_id', $vote->id)
            ->where('member_account_id', $account->id)
            ->exists();

        return response()->json([
            'vote' => [
                'id' => $vote->id,
                'session_id' => $vote->session_id,
                'session_document_id' => $vote->session_document_id,
                'committee_report_agenda_item_id' => $vote->committee_report_agenda_item_id,
                'agenda_type' => $vote->committee_report_agenda_item_id
                    ? 'committee_report'
                    : 'document',
                'motion_title' => $vote->motion_title,
                'motion_description' => $vote->motion_description,
                'voting_method' => $vote->voting_method,
                'status' => $vote->status,
                'has_voted' => $hasVoted,
            ],
        ]);
    }

    public function votingHistory(Request $request)
    {
        $account = $request->user();

        $records = SessionVoteRecord::with('votingEvent')
            ->where('member_account_id', $account->id)
            ->latest('cast_at')
            ->get();

        return response()->json([
            'voting_history' => $records->map(function ($record) {
                $vote = $record->votingEvent;

                if (!$vote) {
                    return null;
                }

                return [
                    'motion_title' => $vote->motion_title,
                    'voting_method' => $vote->voting_method,
                    'status' => $vote->status,
                    'vote' => $vote->voting_method === 'roll_call'
                        ? $record->vote
                        : null,
                    'cast_at' => $record->cast_at,
                ];
            })->filter()->values(),
        ]);
    }

    public function progress($voteId)
    {
        $vote = SessionVote::findOrFail($voteId);

        $records = SessionVoteRecord::where('session_vote_id', $vote->id);

        $eligibleIds = SessionVoteEligibility::where('session_vote_id', $vote->id)
            ->where('eligible', true)
            ->pluck('member_account_id');

        $eligibleCount = $eligibleIds->count();

        $yes = (clone $records)->where('vote', 'yes')->count();
        $no = (clone $records)->where('vote', 'no')->count();
        $abstain = (clone $records)->where('vote', 'abstain')->count();
        $ballots = (clone $records)->count();

        $response = [
            'status' => $vote->status,
            'yes' => $yes,
            'no' => $no,
            'abstain' => $abstain,
            'ballots' => $ballots,
            'eligible' => $eligibleCount,
            'pending' => max(0, $eligibleCount - $ballots),
            'percentage' => $eligibleCount > 0
                ? round(($ballots / $eligibleCount) * 100)
                : 0,
        ];

        // Only return individual identities and choices for roll-call voting.
        if ($vote->voting_method === 'roll_call') {
            $members = MembersAccount::with('member')
                ->whereIn('id', $eligibleIds)
                ->orderBy('id')
                ->get();

            $recordsByMember = SessionVoteRecord::where('session_vote_id', $vote->id)
                ->get()
                ->keyBy('member_account_id');

            $response['members'] = $members->map(function ($member) use ($recordsByMember) {
                $record = $recordsByMember->get($member->id);

                return [
                    'id' => $member->id,
                    'name' => $member->member?->name ?? $member->email,
                    'email' => $member->email,
                    'vote' => $record?->vote,
                    'cast_at' => $record?->cast_at?->format('M d, Y h:i A'),
                    'has_voted' => (bool) $record,
                ];
            })->values();
        }

        return response()->json($response);
    }

    public function report($voteId)
    {
        $vote = SessionVote::query()
            ->where('status', 'finalized')
            ->findOrFail($voteId);

        $session = SessionMeeting::findOrFail($vote->session_id);

        // Retrieve the agenda item title.
        $agendaTitle = 'Agenda Item';

        if ($vote->session_document_id) {
            $agendaItem = SessionDocument::with('document')
                ->where('session_id', $session->id)
                ->find($vote->session_document_id);

            $agendaTitle = $agendaItem?->document?->title
                ?? 'Council Agenda Item';
        } elseif ($vote->committee_report_agenda_item_id) {
            $reportItem = CommitteeReportAgendaItem::with(
                'referral.committee'
            )
                ->where('session_id', $session->id)
                ->find($vote->committee_report_agenda_item_id);

            $agendaTitle = $reportItem?->referral?->committee?->name
                ? 'Committee Report - ' . $reportItem->referral->committee->name
                : 'Committee Report';
        }

        // Retrieve final voting tally.
        $result = is_array($vote->result)
            ? $vote->result
            : (json_decode($vote->result ?? '', true) ?: []);

        $yes = (int) ($result['yes'] ?? 0);
        $no = (int) ($result['no'] ?? 0);
        $abstain = (int) ($result['abstain'] ?? 0);
        $totalVotes = (int) ($result['total_votes'] ?? ($yes + $no + $abstain));

        // Retrieve eligible voters.
        $eligibleIds = SessionVoteEligibility::query()
            ->where('session_vote_id', $vote->id)
            ->where('eligible', true)
            ->pluck('member_account_id');

        $eligibleMembers = MembersAccount::with('member')
            ->whereIn('id', $eligibleIds)
            ->orderBy('id')
            ->get();

        // Individual choices are disclosed only for roll-call voting.
        $individualRecords = collect();

        if ($vote->voting_method === 'roll_call') {
            $individualRecords = SessionVoteRecord::query()
                ->where('session_vote_id', $vote->id)
                ->with('memberAccount.member')
                ->get()
                ->keyBy('member_account_id');
        }

        // Retrieve session attendance.
        $attendance = SessionAttendance::query()
            ->where('session_id', $session->id)
            ->orderBy('attendee_role')
            ->orderBy('attendee_name')
            ->get();

        $attendanceCount = $attendance->count();

        $presentCount = $attendance->whereIn(
            'attendance_status',
            ['present', 'late']
        )->count();

        $absentCount = $attendance->where(
            'attendance_status',
            'absent'
        )->count();

        $pendingCount = max(0, $eligibleMembers->count() - $totalVotes);

        $pdf = Pdf::loadView('secretary.reports.voting-results', [
            'vote' => $vote,
            'session' => $session,
            'agendaTitle' => $agendaTitle,
            'result' => $result,
            'yes' => $yes,
            'no' => $no,
            'abstain' => $abstain,
            'totalVotes' => $totalVotes,
            'eligibleMembers' => $eligibleMembers,
            'individualRecords' => $individualRecords,
            'attendance' => $attendance,
            'attendanceCount' => $attendanceCount,
            'presentCount' => $presentCount,
            'absentCount' => $absentCount,
            'pendingCount' => $pendingCount,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = 'Council-Voting-Results-' . $vote->id . '.pdf';

        return $pdf->stream($filename);
    }

    public function submitForSigning(Request $request, $voteId)
    {
        $vote = SessionVote::query()
            ->where('status', 'finalized')
            ->findOrFail($voteId);

        // Check whether this vote already has a signing request.
        $existingRequest = DocumentSigningRequest::query()
            ->where('document_type', 'voting_results')
            ->where('document_id', $vote->id)
            ->latest('id')
            ->first();

        if ($existingRequest) {
            if (in_array($existingRequest->status, [
                'pending',
                'partially_signed',
                'draft',
            ], true)) {
                return redirect()
                    ->route('document-signing.show', $existingRequest)
                    ->with('info', 'This Voting Results document is already undergoing electronic signing.');
            }

            if ($existingRequest->status === 'fully_signed') {
                return redirect()
                    ->route('document-signing.show', $existingRequest)
                    ->with('info', 'This Voting Results document has already been fully signed.');
            }
        }

        $session = SessionMeeting::findOrFail($vote->session_id);

        if ($session->status === 'suspended') {
            throw ValidationException::withMessages([
                'vote' => 'Voting results cannot be submitted for electronic signing while the session is suspended. Resume the session first.',
            ]);
        }

        $sourcePath = null;
        $signingRequestCreated = false;

        try {
            // Resolve agenda title.
            $agendaTitle = 'Agenda Item';

            if ($vote->session_document_id) {
                $agendaItem = SessionDocument::with('document')
                    ->where('session_id', $session->id)
                    ->find($vote->session_document_id);

                $agendaTitle = $agendaItem?->document?->title
                    ?? 'Council Agenda Item';

            } elseif ($vote->committee_report_agenda_item_id) {
                $reportItem = CommitteeReportAgendaItem::with(
                    'referral.committee'
                )
                    ->where('session_id', $session->id)
                    ->find($vote->committee_report_agenda_item_id);

                $agendaTitle = $reportItem?->referral?->committee?->name
                    ? 'Committee Report - ' . $reportItem->referral->committee->name
                    : 'Committee Report';
            }

            // Retrieve the finalized voting tally.
            $result = is_array($vote->result)
                ? $vote->result
                : (json_decode($vote->result ?? '', true) ?: []);

            $yes = (int) ($result['yes'] ?? 0);
            $no = (int) ($result['no'] ?? 0);
            $abstain = (int) ($result['abstain'] ?? 0);

            $totalVotes = (int) (
                $result['total_votes'] ?? ($yes + $no + $abstain)
            );

            // Retrieve eligible voters.
            $eligibleIds = SessionVoteEligibility::query()
                ->where('session_vote_id', $vote->id)
                ->where('eligible', true)
                ->pluck('member_account_id');

            $eligibleMembers = MembersAccount::with('member')
                ->whereIn('id', $eligibleIds)
                ->orderBy('id')
                ->get();

            // Individual choices are disclosed only for roll-call voting.
            $individualRecords = collect();

            if ($vote->voting_method === 'roll_call') {
                $individualRecords = SessionVoteRecord::query()
                    ->where('session_vote_id', $vote->id)
                    ->with('memberAccount.member')
                    ->get()
                    ->keyBy('member_account_id');
            }

            // Retrieve session attendance.
            $attendance = SessionAttendance::query()
                ->where('session_id', $session->id)
                ->orderBy('attendee_role')
                ->orderBy('attendee_name')
                ->get();

            $attendanceCount = $attendance->count();

            $presentCount = $attendance->whereIn(
                'attendance_status',
                ['present', 'late']
            )->count();

            $absentCount = $attendance->where(
                'attendance_status',
                'absent'
            )->count();

            $pendingCount = max(
                0,
                $eligibleMembers->count() - $totalVotes
            );

            // Generate the same PDF used by the existing report.
            $pdfContents = Pdf::loadView(
                'secretary.reports.voting-results',
                [
                    'vote' => $vote,
                    'session' => $session,
                    'agendaTitle' => $agendaTitle,
                    'result' => $result,
                    'yes' => $yes,
                    'no' => $no,
                    'abstain' => $abstain,
                    'totalVotes' => $totalVotes,
                    'eligibleMembers' => $eligibleMembers,
                    'individualRecords' => $individualRecords,
                    'attendance' => $attendance,
                    'attendanceCount' => $attendanceCount,
                    'presentCount' => $presentCount,
                    'absentCount' => $absentCount,
                    'pendingCount' => $pendingCount,
                ]
            )
                ->setPaper('A4', 'portrait')
                ->output();

            if (empty($pdfContents)) {
                throw new \RuntimeException(
                    'Voting Results PDF generation returned empty content.'
                );
            }

            // Store the immutable source PDF privately.
            $sourcePath = sprintf(
                'official-voting-results/source-vote-%d-%s.pdf',
                $vote->id,
                (string) Str::uuid()
            );

            $stored = Storage::disk('private')->put(
                $sourcePath,
                $pdfContents
            );

            if (
                ! $stored ||
                ! Storage::disk('private')->exists($sourcePath)
            ) {
                throw new \RuntimeException(
                    'Unable to store the Voting Results PDF for signing.'
                );
            }

            // Create the sequential signing request.
            $signingRequest = app(DocumentSigningService::class)->createRequest(
                documentType: 'voting_results',
                documentId: (int) $vote->id,
                documentTitle: 'Official Voting Results - ' . $vote->motion_title,
                documentPath: $sourcePath,
                initiator: $request->user(),
                committeeId: null,
                referenceNo: 'VOTE-' . $vote->id
            );

            $signingRequestCreated = true;

            Log::info('Voting Results submitted for electronic signing.', [
                'vote_id' => $vote->id,
                'session_id' => $session->id,
                'signing_request_id' => $signingRequest->id,
                'source_pdf' => $sourcePath,
                'initiated_by' => $request->user()->id,
            ]);

            return redirect()
                ->route('document-signing.show', $signingRequest)
                ->with(
                    'success',
                    'Official Voting Results PDF generated and submitted for sequential electronic signing. The Secretary must sign first, followed by the Presiding Officer.'
                );

        } catch (Throwable $e) {

            // Delete the source only if request creation did not succeed.
            if (
                ! $signingRequestCreated &&
                $sourcePath &&
                Storage::disk('private')->exists($sourcePath)
            ) {
                Storage::disk('private')->delete($sourcePath);
            }

            Log::error('Failed to submit Voting Results for signing.', [
                'vote_id' => $vote->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                'Unable to submit Voting Results for electronic signing. Please check the application logs.'
            );
        }
    }

}
