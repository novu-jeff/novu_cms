<?php

namespace App\Http\Controllers;

use App\Models\SessionMeeting;
use App\Models\SessionDocument;
use App\Models\SessionMinute;
use App\Models\SessionVote;
use App\Models\SessionAttendance;
use App\Models\SessionMinuteReview;
use App\Models\OrganizationSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\SessionRecording;
use App\Models\SessionTranscript;
use App\Services\SessionMinutesDraftService;
use App\Models\DocumentSigningRequest;
use App\Models\SessionProceeding;
use App\Models\CommitteeReportAgendaItem;
use App\Models\SessionCommitteeReportMinute;
use App\Services\DocumentSigning\DocumentSigningService;
use Throwable;



class SecretaryMinutesController extends Controller
{
    public function index($id)
    {
        $session = SessionMeeting::findOrFail($id);

        $attendance = SessionAttendance::where('session_id', $id)
            ->orderBy('attendee_role')
            ->orderBy('attendee_name')
            ->get();

        $votesByAgenda = $this->getFinalizedVotes($id);

        $documents = SessionDocument::where('session_id', $id)
            ->with('document.member')
            ->orderBy('agenda_order')
            ->get();

        $minutes = SessionMinute::where('session_id', $id)
            ->get()
            ->keyBy('document_id');

        $proceedingsByAgenda = SessionProceeding::with('recorder:id,name')
            ->where('session_id', $id)
            ->where('agenda_type', 'document')
            ->orderBy('created_at')
            ->get()
            ->groupBy('agenda_item_id');   
        
        $committeeReports = CommitteeReportAgendaItem::query()
            ->with([
                'referral.committee',
                'referral.sessionDocument.document',
            ])
            ->where('session_id', $id)
            ->orderBy('agenda_order')
            ->get();

        $committeeReportMinutes = SessionCommitteeReportMinute::where(
            'session_id',
            $id
        )
            ->get()
            ->keyBy('committee_report_agenda_item_id');    

        $committeeProceedingsByAgenda = SessionProceeding::with('recorder:id,name')
            ->where('session_id', $id)
            ->where('agenda_type', 'committee_report')
            ->orderBy('created_at')
            ->get()
            ->groupBy('agenda_item_id');

        return view('secretary.sessions.minutes', compact(
            'session',
            'documents',
            'minutes',
            'votesByAgenda',
            'attendance',
            'proceedingsByAgenda',
            'committeeReports',
            'committeeProceedingsByAgenda',
            'committeeReportMinutes'
        ));
    }

    public function store(Request $request, $id)
    {
        $session = SessionMeeting::findOrFail($id);

        if ($session->minutes_status === 'finalized') {
            return back()->with(
                'error',
                'These minutes have been finalized and can no longer be edited.'
            );
        }

        // Prevent editing while electronic signing is in progress.
        $hasActiveSigningRequest = DocumentSigningRequest::query()
            ->where('document_type', 'meeting_minutes')
            ->where('document_id', $session->id)
            ->whereIn('status', ['draft', 'pending', 'partially_signed'])
            ->exists();

        if ($hasActiveSigningRequest) {
            return back()->with(
                'error',
                'These minutes are currently undergoing electronic signing and can no longer be edited.'
            );
        }

        $validated = $request->validate([
            // Session proceedings
            'call_to_order' => ['nullable', 'string'],
            'prayer' => ['nullable', 'string'],
            'roll_call' => ['nullable', 'string'],
            'adjournment_time' => ['nullable', 'string', 'max:50'],

            // Additional minutes fields
            'quorum_confirmation' => [
                'nullable',
                'in:confirmed,not_confirmed',
            ],

            'previous_minutes_status' => [
                'nullable',
                'in:approved,approved_with_corrections,deferred,not_applicable',
            ],

            // Agenda-level minutes
            'minutes' => ['sometimes', 'array'],
            'minutes.*.discussion' => ['nullable', 'string'],
            'minutes.*.motion' => ['nullable', 'string'],
            'minutes.*.motion_moved_by' => ['nullable', 'string', 'max:255'],
            'minutes.*.motion_seconded_by' => ['nullable', 'string', 'max:255'],
            'minutes.*.official_reference' => ['nullable', 'string', 'max:255'],
            'minutes.*.decision' => ['nullable', 'string'],

            // Official committee report minutes
            'committee_report_minutes' => ['sometimes', 'array'],
            'committee_report_minutes.*.discussion' => ['nullable', 'string'],
            'committee_report_minutes.*.motion' => ['nullable', 'string'],
            'committee_report_minutes.*.motion_moved_by' => ['nullable', 'string', 'max:255'],
            'committee_report_minutes.*.motion_seconded_by' => ['nullable', 'string', 'max:255'],
            'committee_report_minutes.*.official_reference' => ['nullable', 'string', 'max:255'],
            'committee_report_minutes.*.decision' => ['nullable', 'string'],

            // Save Draft vs. Save & Review
            'redirect_to_review' => ['sometimes', 'boolean'],
        ]);

        $submittedMinutes = $validated['minutes'] ?? [];

        $submittedCommitteeReportMinutes = $validated['committee_report_minutes'] ?? [];

        /*
         * Validate that each submitted document ID belongs to
         * an agenda item in this session.
         *
         * SessionMinute.document_id stores the underlying document ID,
         * not the session_documents.id.
         */
        $allowedDocumentIds = SessionDocument::where('session_id', $id)
            ->whereNotNull('document_id')
            ->pluck('document_id')
            ->map(fn ($documentId) => (string) $documentId)
            ->all();

        $invalidDocumentIds = array_diff(
            array_map('strval', array_keys($submittedMinutes)),
            $allowedDocumentIds
        );

        if (!empty($invalidDocumentIds)) {
            throw ValidationException::withMessages([
                'minutes' => 'One or more submitted agenda items do not belong to this session.',
            ]);
        }

        $allowedCommitteeReportIds = CommitteeReportAgendaItem::where(
            'session_id',
            $id
        )
            ->pluck('id')
            ->map(fn ($reportId) => (string) $reportId)
            ->all();

        $invalidCommitteeReportIds = array_diff(
            array_map('strval', array_keys($submittedCommitteeReportMinutes)),
            $allowedCommitteeReportIds
        );

        if (!empty($invalidCommitteeReportIds)) {
            throw ValidationException::withMessages([
                'committee_report_minutes' =>
                    'One or more submitted committee reports do not belong to this session.',
            ]);
        }

        DB::transaction(function () use ($session, $validated, $submittedMinutes, $submittedCommitteeReportMinutes) {
            // Save session-level proceedings.
            $session->update([
                'call_to_order' => $validated['call_to_order'] ?? null,
                'prayer' => $validated['prayer'] ?? null,
                'roll_call' => $validated['roll_call'] ?? null,
                'adjournment_time' => $validated['adjournment_time'] ?? null,
                'quorum_confirmation' => $validated['quorum_confirmation'] ?? null,
                'previous_minutes_status' => $validated['previous_minutes_status'] ?? null,
            ]);

            // Save minutes for each submitted agenda item.
            foreach ($submittedMinutes as $docId => $data) {
                SessionMinute::updateOrCreate(
                    [
                        'session_id' => $session->id,
                        'document_id' => $docId,
                    ],
                    [
                        'discussion' => $data['discussion'] ?? null,
                        'motion' => $data['motion'] ?? null,
                        'motion_moved_by' => $data['motion_moved_by'] ?? null,
                        'motion_seconded_by' => $data['motion_seconded_by'] ?? null,
                        'official_reference' => $data['official_reference'] ?? null,
                        'decision' => $data['decision'] ?? null,
                    ]
                );
            }

            // Save official minutes for each committee report.
            foreach ($submittedCommitteeReportMinutes as $reportId => $data) {
                SessionCommitteeReportMinute::updateOrCreate(
                    [
                        'session_id' => $session->id,
                        'committee_report_agenda_item_id' => $reportId,
                    ],
                    [
                        'discussion' => $data['discussion'] ?? null,
                        'motion' => $data['motion'] ?? null,
                        'motion_moved_by' => $data['motion_moved_by'] ?? null,
                        'motion_seconded_by' => $data['motion_seconded_by'] ?? null,
                        'official_reference' => $data['official_reference'] ?? null,
                        'decision' => $data['decision'] ?? null,
                        'prepared_by' => auth()->id(),
                    ]
                );
            }
        });

        if ($request->boolean('redirect_to_review')) {
            return redirect()
                ->route('secretary.minutes.review', $id)
                ->with('success', 'Minutes saved successfully. You can now review the draft.');
        }

        return back()->with('success', 'Minutes draft saved successfully.');
    }

    public function review($id)
    {
        $session = SessionMeeting::findOrFail($id);

        $attendance = SessionAttendance::where('session_id', $id)
            ->orderBy('attendee_role')
            ->orderBy('attendee_name')
            ->get();

        $votesByAgenda = $this->getFinalizedVotes($id);

        $documents = SessionDocument::where('session_id', $id)
            ->with('document.member')
            ->orderBy('agenda_order')
            ->get();

        $minutes = SessionMinute::where('session_id', $id)
            ->get()
            ->keyBy('document_id');
        
        $committeeReports = CommitteeReportAgendaItem::query()
            ->with([
                'referral.committee',
                'referral.sessionDocument.document',
            ])
            ->where('session_id', $id)
            ->orderBy('agenda_order')
            ->get();

        $committeeReportMinutes = SessionCommitteeReportMinute::where(
            'session_id',
            $id
        )
            ->get()
            ->keyBy('committee_report_agenda_item_id');

        $committeeProceedingsByAgenda = SessionProceeding::with('recorder:id,name')
            ->where('session_id', $id)
            ->where('agenda_type', 'committee_report')
            ->orderBy('created_at')
            ->get()
            ->groupBy('agenda_item_id');    

        return view('secretary.sessions.minutes-review', compact(
            'session',
            'documents',
            'minutes',
            'votesByAgenda',
            'attendance',
            'committeeReports',
            'committeeReportMinutes',
            'committeeProceedingsByAgenda'
        ));
    }

    private function getFinalizedVotes($sessionId)
    {
        return SessionVote::where('session_id', $sessionId)
            ->where('status', 'finalized')
            ->orderBy('session_document_id')
            ->orderBy('id')
            ->get()
            ->each(function ($vote) {
                $vote->result_data = json_decode(
                    $vote->result ?? '{}',
                    true
                ) ?: [];
            })
            ->groupBy('session_document_id');
    }

    public function generate($id)
    {
        $session = SessionMeeting::findOrFail($id);

        if ($session->minutes_status !== 'finalized') {
            return back()->with(
                'error',
                'The official minutes PDF is available only after all required signers have completed signing.'
            );
        }

        if (
            ! $session->minutes_pdf_path ||
            ! Storage::disk('private')->exists($session->minutes_pdf_path)
        ) {
            Log::error('Signed official minutes PDF is missing.', [
                'session_id' => $session->id,
                'pdf_path' => $session->minutes_pdf_path,
            ]);

            return back()->with(
                'error',
                'The signed official PDF could not be found. Please contact the system administrator.'
            );
        }

        $fileName = sprintf(
            'Official-Minutes-Session-%s.pdf',
            $session->id
        );

        return Storage::disk('private')->download(
            $session->minutes_pdf_path,
            $fileName,
            ['Content-Type' => 'application/pdf']
        );
    }


    public function submitForReview(Request $request, $id)
    {
        $session = SessionMeeting::findOrFail($id);

        if (!in_array($session->minutes_status, [
            'draft',
            'revision_required',
        ], true)) {
            return back()->with(
                'error',
                'These minutes cannot currently be submitted for review.'
            );
        }

        DB::transaction(function () use ($session, $request) {
            $session->update([
                'minutes_status' => 'for_review',
                'minutes_submitted_by' => $request->user()->id,
                'minutes_submitted_at' => now(),
                'minutes_reviewed_by' => null,
                'minutes_reviewed_at' => null,
            ]);

            SessionMinuteReview::create([
                'session_id' => $session->id,
                'reviewer_id' => $request->user()->id,
                'action' => 'submitted',
                'comments' => null,
            ]);
        });

        return redirect()
            ->route('secretary.minutes.review', $session->id)
            ->with('success', 'Minutes submitted to the Presiding Officer for review.');
    }

    public function reviewResults()
    {
        $sessions = SessionMeeting::whereIn('minutes_status', [
            'reviewed',
            'revision_required',
            'finalized',
        ])
            ->with(['minuteReviews' => function ($query) {
                $query->latest();
            }])
            ->orderByDesc('updated_at')
            ->get();

        $signingRequests = DocumentSigningRequest::where(
            'document_type',
            'meeting_minutes'
        )
            ->whereIn('document_id', $sessions->pluck('id'))
            ->orderByDesc('id')
            ->get()
            ->keyBy('document_id');

        foreach ($sessions as $session) {
            $session->setRelation(
                'signingRequest',
                $signingRequests->get($session->id)
            );
        }

        return view(
            'secretary.sessions.minutes-review-results',
            compact('sessions')
        );
    }

    public function returned()
    {
        $sessions = SessionMeeting::where(
            'minutes_status',
            'revision_required'
        )
        ->with(['minuteReviews' => function ($query) {
            $query->where('action', 'returned')
                ->latest();
        }])
        ->orderByDesc('updated_at')
        ->get();

        return view(
            'secretary.sessions.minutes-returned',
            compact('sessions')
        );
    }

   public function finalize(Request $request, $id)
    {
        $session = SessionMeeting::findOrFail($id);

        if ($session->minutes_status === 'finalized') {
            return redirect()
                ->route('minutes.review-results')
                ->with('error', 'These minutes have already been finalized.');
        }

        if ($session->minutes_status !== 'reviewed') {
            return back()->with(
                'error',
                'Only minutes marked as reviewed can be submitted for signing.'
            );
        }

        $sourcePath = null;
        $signingRequestCreated = false;

        try {
            // Preserve the exact content of the existing minutes PDF.
            $documents = SessionDocument::where('session_id', $session->id)
                ->with('document.member')
                ->orderBy('agenda_order')
                ->get();

            $minutes = SessionMinute::where('session_id', $session->id)
                ->get()
                ->keyBy('document_id');

            $committeeReports = CommitteeReportAgendaItem::query()
                ->with([
                    'referral.committee',
                    'referral.sessionDocument.document',
                ])
                ->where('session_id', $session->id)
                ->orderBy('agenda_order')
                ->get();

            $committeeReportMinutes = SessionCommitteeReportMinute::where(
                'session_id',
                $session->id
            )
                ->get()
                ->keyBy('committee_report_agenda_item_id');    

            $attendance = SessionAttendance::where('session_id', $session->id)
                ->orderBy('attendee_role')
                ->orderBy('attendee_name')
                ->get();

            $votesByAgenda = $this->getFinalizedVotes($session->id);

            $organization = OrganizationSetting::first();

            $pdfContents = Pdf::loadView(
                'secretary.sessions.minutes-pdf',
                compact(
                    'session',
                    'documents',
                    'minutes',
                    'attendance',
                    'votesByAgenda',
                    'organization',
                    'committeeReports',
                    'committeeReportMinutes'
                )
            )->output();

            if (empty($pdfContents)) {
                throw new \RuntimeException(
                    'The minutes PDF generation returned empty content.'
                );
            }

            // Store the frozen source PDF on private storage.
            $sourcePath = sprintf(
                'official-minutes/source-session-%d-%s.pdf',
                $session->id,
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
                    'Unable to store the minutes PDF for signing.'
                );
            }

            // Initiate sequential signing.
            // Signers are resolved by DocumentSignerResolver.
            $signingRequest = app(DocumentSigningService::class)->createRequest(
                documentType: 'meeting_minutes',
                documentId: (int) $session->id,
                documentTitle: 'Official Meeting Minutes - ' . $session->title,
                documentPath: $sourcePath,
                initiator: $request->user(),
                committeeId: null,
                referenceNo: $session->session_number
                    ? (string) $session->session_number
                    : null
            );

            $signingRequestCreated = true;

            Log::info('Meeting minutes submitted for electronic signing.', [
                'session_id' => $session->id,
                'signing_request_id' => $signingRequest->id,
                'source_pdf' => $sourcePath,
                'initiated_by' => $request->user()->id,
            ]);

            return redirect()
                ->route('minutes.review-results')
                ->with(
                    'success',
                    'Minutes PDF generated successfully and submitted for sequential electronic signing. The Secretary must sign first, followed by the Presiding Officer.'
                );

        } catch (Throwable $e) {

            // Only remove the source if request creation did not succeed.
            if (
                ! $signingRequestCreated &&
                $sourcePath &&
                Storage::disk('private')->exists($sourcePath)
            ) {
                Storage::disk('private')->delete($sourcePath);
            }

            Log::error('Failed to submit meeting minutes for signing.', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                'Unable to submit minutes for signing: ' . $e->getMessage()
            );
        }
    }

    public function generateDraft(
        Request $request,
        $id,
        SessionMinutesDraftService $draftService
    ) {
        $session = SessionMeeting::findOrFail($id);

        if ($session->minutes_status === 'finalized') {
            return back()->with(
                'error',
                'Finalized minutes cannot be regenerated.'
            );
        }

        $validated = $request->validate([
            'replace_existing' => ['nullable', 'boolean'],
        ]);

        $recording = $session->recordings()
            ->whereHas('transcript', function ($query) {
                $query->where('status', 'completed')
                    ->whereNotNull('transcript_text');
            })
            ->with('transcript')
            ->latest()
            ->first();

        if (! $recording || ! trim($recording->transcript->transcript_text ?? '')) {
            return back()->with(
                'error',
                'No completed transcript is available for this session.'
            );
        }

        $existingMinutes = \App\Models\SessionMinute::where(
            'session_id',
            $session->id
        )->get();

        $hasExistingContent =
            $existingMinutes->contains(fn ($minute) =>
                collect([
                    $minute->discussion,
                    $minute->motion,
                    $minute->motion_moved_by,
                    $minute->motion_seconded_by,
                    $minute->official_reference,
                    $minute->decision,
                ])->contains(fn ($value) => filled($value))
            )
            || collect([
                $session->call_to_order,
                $session->prayer,
                $session->roll_call,
                $session->adjournment_time,
                $session->quorum_confirmation,
                $session->previous_minutes_status,
            ])->contains(fn ($value) => filled($value));

        if ($hasExistingContent && ! ($validated['replace_existing'] ?? false)) {
            return back()->with(
                'error',
                'Minutes already contain content. Confirm replacement before generating a new draft.'
            );
        }

        try {
            $draft = $draftService->generate(
                $session,
                $recording->transcript->transcript_text
            );

            DB::transaction(function () use ($session, $draft) {
                $session->update([
                    'call_to_order' => $draft['call_to_order'] ?? null,
                    'prayer' => $draft['prayer'] ?? null,
                    'roll_call' => $draft['roll_call'] ?? null,
                    'adjournment_time' => $draft['adjournment_time'] ?? null,
                    'quorum_confirmation' => $draft['quorum_confirmation'] ?? null,
                    'previous_minutes_status' => in_array(
                        $draft['previous_minutes_status'] ?? null,
                        [
                            'approved',
                            'approved_with_corrections',
                            'deferred',
                            'not_applicable',
                        ],
                        true
                    ) ? $draft['previous_minutes_status'] : null,
                ]);

                foreach ($draft['minutes'] as $item) {
                    $values = [
                        'discussion' => $item['discussion'] ?? null,
                        'motion' => $item['motion'] ?? null,
                        'motion_moved_by' => $item['motion_moved_by'] ?? null,
                        'motion_seconded_by' => $item['motion_seconded_by'] ?? null,
                        'official_reference' => $item['official_reference'] ?? null,
                        'decision' => $item['decision'] ?? null,
                    ];

                    \App\Models\SessionMinute::updateOrCreate(
                        [
                            'session_id' => $session->id,
                            'document_id' => (int) $item['document_id'],
                        ],
                        $values
                    );
                }

                if ($session->minutes_status !== 'revision_required') {
                    $session->update(['minutes_status' => 'draft']);
                }
            });
        } catch (\Throwable $e) {
            Log::error('AI minutes draft generation failed', [
                'session_id' => $session->id,
                'exception' => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                'Unable to generate the draft. Check the application logs and AI configuration.'
            );
        }

        return redirect()
            ->route('secretary.minutes.index', $session->id)
            ->with(
                'success',
                'AI-generated minutes draft created. Review and correct it before submission.'
            );
    }


}
