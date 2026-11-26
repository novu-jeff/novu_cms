<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\CommitteeReferral;
use App\Models\SessionMeeting;
use App\Models\CommitteeReportAgendaItem;
use App\Models\CommitteeMeetingAgendaItem;
use App\Models\DocumentSigningRequest;
use App\Services\DocumentSigning\DocumentSigningService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class CommitteeReferralController extends Controller
{
    public function index(Request $request)
    {
        $query = CommitteeReferral::with([
            'committee',
            'sessionDocument.document',
        ])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('committee_id')) {
            $query->where('committee_id', $request->committee_id);
        }

        $referrals = $query->paginate(15)->withQueryString();

        $committees = Committee::where('isActive', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        $counts = CommitteeReferral::selectRaw(
            'status, COUNT(*) as total'
        )
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('secretary.committee-referrals.index', compact(
            'referrals',
            'committees',
            'counts'
        ));
    }

    public function show($id)
    {
        $referral = CommitteeReferral::with([
            'committee',
            'sessionDocument.document',
            'meetingReports.meeting',
        ])->findOrFail($id);

        return view(
            'secretary.committee-referrals.show',
            compact('referral')
        );
    }

    public function startReview($id)
    {
        $referral = CommitteeReferral::findOrFail($id);

        if ($referral->status !== 'referred') {
            return back()->with(
                'error',
                'Only newly referred items can be moved to review.'
            );
        }

        $referral->update(['status' => 'under_review']);

        return back()->with('success', 'Referral marked under review.');
    }

    public function submitReport(Request $request, $id)
    {
        $referral = CommitteeReferral::findOrFail($id);

        if (! in_array($referral->status, [
            'referred',
            'under_review',
        ], true)) {
            return back()->with(
                'error',
                'This referral is not accepting a report.'
            );
        }

        // Require at least one completed committee meeting
        // containing this referral as an agenda item.
        $completedMeetingExists = CommitteeMeetingAgendaItem::where(
            'committee_referral_id',
            $referral->id
        )
            ->whereHas('meeting', function ($query) {
                $query->where('status', 'completed');
            })
            ->exists();

        if (! $completedMeetingExists) {
            return back()->with(
                'error',
                'The committee must complete its meeting and deliberations before submitting the official report.'
            );
        }

        $validated = $request->validate([
            'recommendation' => [
                'required',
                'string',
                'min:3',
                'max:10000',
            ],
            'report' => [
                'required',
                'file',
                'mimes:pdf',
                'max:10240',
            ],
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

        return back()->with('success', 'Committee report submitted.');
    }

    public function downloadReport($id)
    {
        $referral = CommitteeReferral::findOrFail($id);

        abort_unless(
            $referral->report_file_path &&
            Storage::exists($referral->report_file_path),
            404,
            'Report file not found.'
        );

        return Storage::download(
            $referral->report_file_path,
            'committee-report-' . $referral->id . '.pdf'
        );
    }


    
    public function submitForSigning(Request $request, $id)
    {
        $referral = CommitteeReferral::with([
            'committee',
            'sessionDocument.document',
            'meetingReports.meeting',
        ])->findOrFail($id);

        abort_unless(
            $referral->status === 'report_submitted',
            422,
            'Only submitted committee reports can be sent for signing.'
        );

        // Find the meeting report linked to this referral.
        $meetingReport = $referral->meetingReports()
            ->with('meeting')
            ->latest('committee_meeting_reports.id')
            ->first();

        abort_unless(
            $meetingReport && $meetingReport->meeting,
            422,
            'No uploaded committee meeting report is linked to this referral.'
        );

        $meeting = $meetingReport->meeting;

        abort_unless(
            $meeting->status === 'completed',
            422,
            'Only reports from completed committee meetings can be signed.'
        );

        // An individual report must contain exactly one linked referral.
        $linkedReferralIds = $meetingReport->referrals()
            ->pluck('committee_referrals.id')
            ->unique()
            ->values();

        abort_unless(
            $linkedReferralIds->count() === 1 &&
            (int) $linkedReferralIds->first() === (int) $referral->id,
            422,
            'This meeting report contains multiple referrals. It must be processed as a consolidated committee report.'
        );

        // Prevent duplicate active or completed signing requests.
        $existingRequest = DocumentSigningRequest::query()
            ->where('document_type', 'committee_report')
            ->where('document_id', $referral->id)
            ->latest('id')
            ->first();

        if ($existingRequest) {
            if (in_array($existingRequest->status, [
                'draft',
                'pending',
                'partially_signed',
            ], true)) {
                return redirect()
                    ->route('document-signing.show', $existingRequest)
                    ->with('info', 'This committee report is already undergoing electronic signing.');
            }

            if ($existingRequest->status === 'fully_signed') {
                return redirect()
                    ->route('document-signing.show', $existingRequest)
                    ->with('info', 'This committee report has already been fully signed.');
            }
        }

        $sourcePath = null;
        $signingRequestCreated = false;

        try {
            // Meeting reports are stored on the local disk.
            $sourceDisk = Storage::disk('local');
            $privateDisk = Storage::disk('private');

            if (! $sourceDisk->exists($meetingReport->file_path)) {
                throw new \RuntimeException(
                    'The uploaded committee meeting report PDF could not be found.'
                );
            }

            $pdfContents = $sourceDisk->get($meetingReport->file_path);

            if (! is_string($pdfContents) || empty($pdfContents)) {
                throw new \RuntimeException(
                    'Unable to read the uploaded committee meeting report.'
                );
            }

            // Remove leading whitespace before the PDF header.
            // The original uploaded PDF contains a leading newline.
            $pdfContents = ltrim($pdfContents);

            // Validate the normalized PDF header.
            if (! str_starts_with($pdfContents, '%PDF-')) {
                throw new \RuntimeException(
                    'The committee meeting report is not a valid PDF.'
                );
            }

            // Preserve the original uploaded report.
            // Create a normalized private copy for electronic signing.
            $sourcePath = sprintf(
                'official-committee-reports/referral-%d-%s.pdf',
                $referral->id,
                (string) Str::uuid()
            );

            $stored = $privateDisk->put(
                $sourcePath,
                $pdfContents
            );

            if (
                ! $stored ||
                ! $privateDisk->exists($sourcePath)
            ) {
                throw new \RuntimeException(
                    'Unable to store the committee report for electronic signing.'
                );
            }

            $documentTitle = 'Individual Committee Report - '
                . ($referral->sessionDocument?->document?->title
                    ?? 'Referral #' . $referral->id);

            $signingRequest = app(DocumentSigningService::class)->createRequest(
                documentType: 'committee_report',
                documentId: (int) $referral->id,
                documentTitle: $documentTitle,
                documentPath: $sourcePath,
                initiator: $request->user(),
                committeeId: (int) $referral->committee_id,
                referenceNo: 'REF-' . $referral->id
            );

            $signingRequestCreated = true;

            Log::info('Individual Committee Report submitted for electronic signing.', [
                'referral_id' => $referral->id,
                'meeting_id' => $meeting->id,
                'meeting_report_id' => $meetingReport->id,
                'committee_id' => $referral->committee_id,
                'signing_request_id' => $signingRequest->id,
                'source_pdf' => $sourcePath,
                'initiated_by' => $request->user()->id,
            ]);

            return redirect()
                ->route('document-signing.show', $signingRequest)
                ->with(
                    'success',
                    'Individual Committee Report submitted for sequential electronic signing. The Committee Chairperson must sign first, followed by the Presiding Officer.'
                );

        } catch (Throwable $e) {

            if (
                ! $signingRequestCreated &&
                $sourcePath &&
                Storage::disk('private')->exists($sourcePath)
            ) {
                Storage::disk('private')->delete($sourcePath);
            }

            Log::error('Failed to submit Individual Committee Report for signing.', [
                'referral_id' => $referral->id,
                'meeting_id' => $meeting->id,
                'committee_id' => $referral->committee_id,
                'error' => $e->getMessage(),
            ]);

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                throw $e;
            }

            return back()->with(
                'error',
                'Unable to submit the committee report for electronic signing. Please check the application logs.'
            );
        }
    }



    
    public function returnToCouncil($id)
    {
        $referral = CommitteeReferral::findOrFail($id);

        $hasReport = $referral->report_file_path
            || $referral->meetingReports()->exists();
            

        if (
            $referral->status !== 'report_submitted' ||
            ! $referral->recommendation ||
            ! $hasReport
        ) {
            return back()->with(
                'error',
                'A submitted recommendation and committee report are required.'
            );
        }

        $signingRequest = DocumentSigningRequest::query()
            ->where('document_type', 'committee_report')
            ->where('document_id', $referral->id)
            ->latest('id')
            ->first();

        if (! $signingRequest || $signingRequest->status !== 'fully_signed') {
            return back()->with(
                'error',
                'The individual committee report must be fully signed by the Committee Chairperson and Presiding Officer before it can be returned to Council.'
            );
        }

        $referral->update([
            'status' => 'returned_to_council',
        ]);

        return back()->with(
            'success',
            'Committee report accepted and returned to council.'
        );
    }



    public function addToFutureAgenda($id)
    {
        $referral = CommitteeReferral::findOrFail($id);

        $hasReport = $referral->report_file_path
            || $referral->meetingReports()->exists();

        abort_unless(
            $referral->status === 'returned_to_council'
            && $referral->recommendation
            && $hasReport,
            422,
            'Only referrals with a returned committee report can be added to an agenda.'
        );

        $sessions = SessionMeeting::where('status', 'upcoming')
            ->whereDate('session_date', '>=', today())
            ->orderBy('session_date')
            ->get(['id', 'title', 'session_date']);

        return view(
            'secretary.committee-referrals.add-to-agenda',
            compact('referral', 'sessions')
        );
    }

    public function storeInFutureAgenda(Request $request, $id)
    {
        $validated = $request->validate([
            'session_id' => [
                'required',
                'integer',
                'exists:session_meetings,id',
            ],
        ]);

        $referral = CommitteeReferral::findOrFail($id);

        $hasReport = $referral->report_file_path
            || $referral->meetingReports()->exists();

        abort_unless(
            $referral->status === 'returned_to_council'
            && $referral->recommendation
            && $hasReport,
            422,
            'Only referrals with a returned committee report can be added to an agenda.'
        );

        $session = SessionMeeting::findOrFail($validated['session_id']);

        abort_unless(
            $session->status === 'upcoming'
            && $session->session_date >= today()->toDateString(),
            422,
            'Select a future session that is still upcoming.'
        );

        DB::transaction(function () use ($referral, $session) {
            $existing = CommitteeReportAgendaItem::where(
                'committee_referral_id',
                $referral->id
            )->where('session_id', $session->id)->exists();

            if ($existing) {
                throw ValidationException::withMessages([
                    'session_id' => 'This committee report is already on that session agenda.',
                ]);
            }

            $order = (int) (
                DB::table('session_documents')
                    ->where('session_id', $session->id)
                    ->max('agenda_order') ?? 0
            );

            $reportOrder = (int) (
                CommitteeReportAgendaItem::where('session_id', $session->id)
                    ->max('agenda_order') ?? 0
            );

            CommitteeReportAgendaItem::create([
                'committee_referral_id' => $referral->id,
                'session_id' => $session->id,
                'agenda_order' => max($order, $reportOrder) + 1,
            ]);
        });

        return redirect()
            ->route('secretary.session.agenda', $session->id)
            ->with('success', 'Committee report added to the future session agenda.');
    }

}
