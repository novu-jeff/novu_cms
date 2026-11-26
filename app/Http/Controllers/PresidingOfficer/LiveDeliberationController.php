<?php

namespace App\Http\Controllers\PresidingOfficer;

use App\Http\Controllers\Controller;
use App\Models\CommitteeReportAgendaItem;
use App\Models\MembersAccount;
use App\Models\SessionAttendance;
use App\Models\SessionDocument;
use App\Models\SessionMeeting;
use App\Models\SessionSuspension;
use App\Models\SessionVote;
use App\Models\SessionProceeding;
use App\Models\SessionPreviousMinutesAgendaItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LiveDeliberationController extends Controller
{
    /**
     * Show sessions assigned to the authenticated Presiding Officer.
     */
    public function index(Request $request)
    {
        $member = $this->presidingOfficerMember($request);

        $sessions = SessionMeeting::query()
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->get()
            ->filter(fn ($session) => $this->isAssignedToMember(
                $session,
                $member->name
            ))
            ->values();

        $activeSessions = $sessions
            ->whereIn('status', ['ongoing', 'suspended', 'upcoming'])
            ->values();

        $completedSessions = $sessions
            ->where('status', 'completed')
            ->values();

        return view('presiding-officer.live-deliberation.index', [
            'member' => $member,
            'activeSessions' => $activeSessions,
            'completedSessions' => $completedSessions,
        ]);
    }

    /**
     * Show the read-only live deliberation workspace for one assigned session.
     */
    public function show(Request $request, int $sessionId)
    {
        $member = $this->presidingOfficerMember($request);

        $session = SessionMeeting::findOrFail($sessionId);

        abort_unless(
            $this->isAssignedToMember($session, $member->name),
            403,
            'This session is not assigned to your office.'
        );

        // Regular document agenda items.
        $documents = SessionDocument::with('document')
            ->where('session_id', $session->id)
            ->orderBy('agenda_order')
            ->get()
            ->map(function ($item) {
                $item->agenda_type = 'document';
                $item->title = $item->document?->title ?? 'Untitled Document';

                return $item;
            });

        // Committee report agenda items.
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
                    'title' => $item->referral
                        ?->sessionDocument
                        ?->document
                        ?->title ?? 'Committee Report',
                    'committee_name' => $item->referral
                        ?->committee
                        ?->name ?? 'Unassigned Committee',
                    'referral_id' => $item->committee_referral_id,
                    'report_file_path' => $item->referral
                        ?->report_file_path,
                ];
            });

        // Previous Minutes agenda items.
            $previousMinutes = SessionPreviousMinutesAgendaItem::with('previousSession')
                ->where('session_id', $session->id)
                ->whereHas('previousSession', function ($query) {
                    $query->where('minutes_status', 'finalized')
                        ->whereNotNull('minutes_pdf_path');
                })
                ->orderBy('agenda_order')
                ->get()
                ->map(function ($item) {
                    $previousSession = $item->previousSession;

                    return (object) [
                        'id' => $item->id,
                        'agenda_type' => 'previous_minutes',
                        'agenda_order' => $item->agenda_order,
                        'status' => $item->status,
                        'title' => 'Previous Minutes - '
                            . ($previousSession?->title ?? 'Previous Session'),
                        'previous_session_id' => $item->previous_session_id,
                        'previous_session_title' => $previousSession?->title,
                        'previous_session_date' => $previousSession?->session_date,
                        'decision' => $item->decision,
                    ];
                });    

        $agendaItems = $documents
            ->concat($committeeReports)
            ->concat($previousMinutes)
            ->sortBy('agenda_order')
            ->values();

        $currentType = $session->current_agenda_item_type ?: 'document';

        $currentAgendaItem = $agendaItems->first(
            fn ($item) =>
                (int) $item->id === (int) $session->current_agenda_item_id
                && $item->agenda_type === $currentType
        );

        // Quorum monitoring.
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

        $totalMembers = MembersAccount::query()
            ->where('is_active', 1)
            ->whereHas('member', function ($query) {
                $query->whereNotNull('position')
                    ->where('position', '<>', '');
            })
            ->count();

        $attendances = SessionAttendance::with('memberAccount')
            ->where('session_id', $session->id)
            ->orderBy('id')
            ->get();

        // Suspension history and currently active suspension.
        $suspensions = SessionSuspension::with([
            'suspendedBy:id,name',
            'resumedBy:id,name',
        ])
            ->where('session_id', $session->id)
            ->latest('suspended_at')
            ->get();

        $activeSuspension = $suspensions->first(
            fn ($suspension) => $suspension->resumed_at === null
        );

        // Voting information is displayed only; this workspace does not
        // open, close, cast, or finalize votes.
        $votes = SessionVote::withCount([
            'records',
            'eligibilities',
        ])
            ->where('session_id', $session->id)
            ->latest('opened_at')
            ->get();

        $proceedings = SessionProceeding::with('recorder:id,name')
            ->where('session_id', $session->id)
            ->orderBy('created_at')
            ->get();    

        return view('presiding-officer.live-deliberation.show', [
            'member' => $member,
            'session' => $session,
            'agendaItems' => $agendaItems,
            'currentAgendaItem' => $currentAgendaItem,
            'presentCount' => $presentCount,
            'totalMembers' => $totalMembers,
            'quorumRequired' => $quorumRequired,
            'quorumMet' => $quorumMet,
            'attendances' => $attendances,
            'suspensions' => $suspensions,
            'activeSuspension' => $activeSuspension,
            'votes' => $votes,
            'proceedings' => $proceedings,
        ]);
    }

    /**
     * Resolve and validate the authenticated Vice Mayor's member record.
     */
    private function presidingOfficerMember(Request $request)
    {
        $user = $request->user();

        abort_unless(
            $user && $user->isPresidingOfficer(),
            403,
            'Presiding Officer access is required.'
        );

        $member = $user->member;

        abort_unless(
            $member
            && strtolower(trim($member->position ?? '')) === 'vice mayor',
            403,
            'Your account must be linked to the Vice Mayor member record.'
        );

        return $member;
    }

    /**
     * Check whether a session belongs to the given member.
     */
    private function isAssignedToMember(
        SessionMeeting $session,
        string $memberName
    ): bool {
        return $this->normalizeOfficerName($session->presiding_officer)
            === $this->normalizeOfficerName($memberName);
    }

    /**
     * Normalize historical officer names such as "Hon. John Doe".
     */
    private function normalizeOfficerName(?string $name): string
    {
        $name = trim((string) $name);

        $name = preg_replace(
            '/^(hon\.?|honorable)\s+/i',
            '',
            $name
        );

        $name = preg_replace('/\s+/', ' ', trim($name));

        return mb_strtolower($name);
    }


    /**
     * Record a proceedings entry for the current agenda item.
     */
    public function storeProceeding(
        Request $request,
        int $sessionId,
        string $agendaType,
        int $agendaItemId
    ) {
        $member = $this->presidingOfficerMember($request);

        $validated = $request->validate([
            'entry_type' => [
                'required',
                'in:discussion,motion,ruling,remark,decision',
            ],
            'content' => ['required', 'string', 'max:20000'],
            'moved_by' => ['nullable', 'string', 'max:255'],
            'seconded_by' => ['nullable', 'string', 'max:255'],
        ]);

        abort_unless(
            in_array($agendaType, ['document', 'committee_report', 'previous_minutes',], true),
            404,
            'Unsupported agenda type.'
        );

        DB::transaction(function () use (
            $sessionId,
            $agendaType,
            $agendaItemId,
            $member,
            $validated,
            $request
        ) {
            // Lock the parent session to prevent concurrent suspension
            // or agenda changes while this entry is being recorded.
            $session = SessionMeeting::query()
                ->lockForUpdate()
                ->findOrFail($sessionId);

            abort_unless(
                $this->isAssignedToMember($session, $member->name),
                403,
                'This session is not assigned to your office.'
            );

            if ($session->status !== 'ongoing') {
                throw ValidationException::withMessages([
                    'proceedings' => 'Proceedings can only be recorded while the session is ongoing.',
                ]);
            }

            $currentType = $session->current_agenda_item_type ?: 'document';

            if (
                $currentType !== $agendaType ||
                (int) $session->current_agenda_item_id !== $agendaItemId
            ) {
                throw ValidationException::withMessages([
                    'proceedings' => 'You can only record proceedings for the current agenda item.',
                ]);
            }

            // Verify that the agenda item actually belongs to this session.
            if ($agendaType === 'document') {
                SessionDocument::query()
                    ->where('session_id', $session->id)
                    ->findOrFail($agendaItemId);
            } elseif ($agendaType === 'committee_report') {
                CommitteeReportAgendaItem::query()
                    ->where('session_id', $session->id)
                    ->findOrFail($agendaItemId);
            } else {
                SessionPreviousMinutesAgendaItem::query()
                    ->where('session_id', $session->id)
                    ->whereHas('previousSession', function ($query) {
                        $query->where('minutes_status', 'finalized')
                            ->whereNotNull('minutes_pdf_path');
                    })
                    ->findOrFail($agendaItemId);
            }

            SessionProceeding::create([
                'session_id' => $session->id,
                'agenda_item_id' => $agendaItemId,
                'agenda_type' => $agendaType,
                'entry_type' => $validated['entry_type'],
                'content' => $validated['content'],
                'moved_by' => $validated['moved_by'] ?? null,
                'seconded_by' => $validated['seconded_by'] ?? null,
                'recorded_by' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route('presiding-officer.sessions.show', $sessionId)
            ->with('success', 'Proceedings entry recorded successfully.');
    }



    /**
     * Preview an agenda PDF after verifying session assignment and ownership.
     */
    public function previewDocument(
        Request $request,
        int $sessionId,
        string $agendaType,
        int $agendaItemId
    ) {
        $member = $this->presidingOfficerMember($request);

        $session = SessionMeeting::findOrFail($sessionId);

        abort_unless(
            $this->isAssignedToMember($session, $member->name),
            403,
            'This session is not assigned to your office.'
        );

        // Regular legislative document stored in the separate LIS application.
        if ($agendaType === 'document') {
            $agendaItem = SessionDocument::with('document')
                ->where('session_id', $session->id)
                ->findOrFail($agendaItemId);

            $document = $agendaItem->document;

            abort_unless(
                $document && $document->file_path,
                404,
                'Document file not found.'
            );

            $relativePath = preg_replace(
                '#^(storage/|public/)#',
                '',
                ltrim($document->file_path, '/')
            );

            abort_unless(
                strtolower(pathinfo($relativePath, PATHINFO_EXTENSION)) === 'pdf',
                404,
                'Only PDF documents can be previewed.'
            );

            $lisUrl = rtrim(config('app.lis_url'), '/');

            abort_unless($lisUrl, 503, 'LIS document storage URL is not configured.');

            return redirect()->away(
                $lisUrl . '/storage/' . $relativePath
            );
        }

                // Previous Minutes: serve the prior session's finalized signed PDF
        // through the private disk. Do not expose a public storage URL.
        if ($agendaType === 'previous_minutes') {
            $agendaItem = SessionPreviousMinutesAgendaItem::with('previousSession')
                ->where('session_id', $session->id)
                ->findOrFail($agendaItemId);

            $previousSession = $agendaItem->previousSession;

            abort_unless(
                $previousSession
                    && $previousSession->minutes_status === 'finalized'
                    && $previousSession->minutes_pdf_path,
                404,
                'Finalized signed minutes not found.'
            );

            $path = $previousSession->minutes_pdf_path;

            abort_unless(
                strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf',
                404,
                'Only PDF minutes can be previewed.'
            );

            $disk = Storage::disk('private');

            abort_unless(
                $disk->exists($path),
                404,
                'Final signed minutes not found in private storage.'
            );

            $filename = 'previous-minutes-session-' . $previousSession->id . '.pdf';

            return $disk->response(
                $path,
                $filename,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $filename . '"',
                    'X-Content-Type-Options' => 'nosniff',
                    'Cache-Control' => 'private, no-store',
                ],
                'inline'
            );
        }

        
            // Official committee report stored on the private disk.
            if ($agendaType === 'committee_report') {
                $agendaItem = CommitteeReportAgendaItem::with('referral')
                    ->where('session_id', $session->id)
                    ->findOrFail($agendaItemId);

                $referral = $agendaItem->referral;

                abort_unless(
                    $referral,
                    404,
                    'Committee referral not found.'
                );

                // Retrieve the completed electronic signing request.
                $signingRequest = \App\Models\DocumentSigningRequest::query()
                    ->where('document_type', 'committee_report')
                    ->where('document_id', $referral->id)
                    ->where('status', 'fully_signed')
                    ->whereNotNull('signed_document_path')
                    ->latest('id')
                    ->first();

                abort_unless(
                    $signingRequest,
                    404,
                    'The committee report has not been fully signed.'
                );

                $path = $signingRequest->signed_document_path;

                abort_unless(
                    strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf',
                    404,
                    'Only PDF documents can be previewed.'
                );

                $disk = Storage::disk('private');

                abort_unless(
                    $disk->exists($path),
                    404,
                    'Final signed committee report not found in private storage.'
                );

                return $disk->response(
                    $path,
                    'committee-report-' . $referral->id . '-signed.pdf',
                    [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="committee-report-' . $referral->id . '-signed.pdf"',
                        'X-Content-Type-Options' => 'nosniff',
                        'Cache-Control' => 'private, no-store',
                    ],
                    'inline'
                );
            }


        abort(404, 'Unsupported agenda document type.');
    }

}
