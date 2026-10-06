<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SessionMeeting;
use App\Models\SessionDocument;
use App\Models\DocumentRemark;

class SecretaryRemarkController extends Controller
{
    public function index($id)
    {
        $session = SessionMeeting::findOrFail($id);

        $documents = SessionDocument::where('session_id', $id)
            ->with([
                'document.member',
                'document.documentRemarks' => function ($q) {
                    $q->with([
                        'member.member',
                        'replies.member.member',
                    ]);
                },
            ])
            ->orderBy('agenda_order')
            ->get();

       // dd($documents);

        return view(
            'secretary.sessions.remarks',
            compact('session','documents')
        );
    }
}
