<?php

namespace App\Http\Controllers;

use App\Models\CommitteeReferral;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommitteePortalDocumentController extends Controller
{
    public function show($referralId)
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

        /*
         * If a committee report has been submitted,
         * open the committee report PDF.
         */
        if ($referral->report_file_path) {

            abort_unless(
                Storage::exists($referral->report_file_path),
                404,
                'Committee report file not found.'
            );

            return Storage::response(
                $referral->report_file_path,
                'committee-report-' . $referral->id . '.pdf',
                [
                    'Content-Type' => 'application/pdf',
                    'X-Content-Type-Options' => 'nosniff',
                ],
                'inline'
            );
        }

        /*
         * Otherwise, open the original referred document.
         */
        $document = $referral->sessionDocument?->document;

        abort_unless($document && $document->file_path, 404);

        $relativePath = preg_replace(
            '#^(storage/|public/)#',
            '',
            ltrim($document->file_path, '/')
        );

        $fileUrl = rtrim(config('app.lis_url'), '/')
            . '/storage/' . $relativePath;

        return redirect()->away($fileUrl);
    }
}
