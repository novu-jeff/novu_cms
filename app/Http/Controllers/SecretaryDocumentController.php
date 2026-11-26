<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MemberDocument;
use App\Models\SessionDocument;
use App\Models\SessionMeeting;
use App\Models\SessionVote;
use App\Models\SessionVoteEligibility;
use App\Models\SessionVoteRecord;
use Illuminate\Support\Facades\Storage;

class SecretaryDocumentController extends Controller
{
    
    public function dashboard()
    {
        $documents = MemberDocument::whereIn('status', ['forwarded', 'approved'])
            ->with(['member', 'sessionDocument.session'])
            ->latest()
            ->get();

        $sessions = SessionMeeting::whereIn('status', ['upcoming', 'ongoing'])
            ->orderBy('session_date')
            ->get();

        $pendingAgendaItems = $documents
            ->where('status', 'forwarded')
            ->values();

        // Voting summary
        $votes = SessionVote::query()
            ->latest()
            ->get();

        $votingStats = [
            'open' => $votes->where('status', 'open')->count(),
            'closed' => $votes->where('status', 'closed')->count(),
            'finalized' => $votes->where('status', 'finalized')->count(),
        ];

        // Recent votes with participation and result counts
        $recentVotes = $votes->take(5)->map(function ($vote) {
            $vote->eligible_count = SessionVoteEligibility::where(
                'session_vote_id',
                $vote->id
            )->where('eligible', true)->count();

            $vote->ballot_count = SessionVoteRecord::where(
                'session_vote_id',
                $vote->id
            )->count();

            $vote->yes_count = SessionVoteRecord::where('session_vote_id', $vote->id)
                ->where('vote', 'yes')
                ->count();

            $vote->no_count = SessionVoteRecord::where('session_vote_id', $vote->id)
                ->where('vote', 'no')
                ->count();

            $vote->abstain_count = SessionVoteRecord::where('session_vote_id', $vote->id)
                ->where('vote', 'abstain')
                ->count();

            return $vote;
        });

        return view('secretary.dashboard', compact(
            'documents',
            'sessions',
            'pendingAgendaItems',
            'votingStats',
            'recentVotes'
        ));
    }


    
public function index(Request $request)
{
    $search = trim($request->input('search', ''));
    $status = $request->input('status', '');

    // Only allow supported document statuses.
    if (! in_array($status, ['forwarded', 'approved', 'rejected'], true)) {
        $status = '';
    }

    // Base document query.
    $query = MemberDocument::query()
        ->whereIn('status', ['forwarded', 'approved', 'rejected'])
        ->with([
            'member',
            'sessionDocument.session',
        ])
        ->latest();

    // Search by document title or submitting member.
    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%')
                ->orWhereHas('member', function ($memberQuery) use ($search) {
                    $memberQuery->where('name', 'like', '%' . $search . '%');
                });
        });
    }

    // Filter by status.
    if ($status !== '') {
        $query->where('status', $status);
    }

    // Paginate documents.
    $documents = $query->paginate(15)->withQueryString();

    // Load upcoming sessions once for the approval and reassignment modals.
    $upcomingSessions = SessionMeeting::query()
        ->where('status', 'upcoming')
        ->orderBy('session_date')
        ->get();

    return view('secretary.documents.index', compact(
        'documents',
        'search',
        'status',
        'upcomingSessions'
    ));
}


    public function updateSession(Request $request, $id)
    {
        $request->validate([
            'session_id' => 'required|exists:session_meetings,id',
        ]);

        $document = MemberDocument::findOrFail($id);
        $sessionDocument = SessionDocument::where('document_id', $document->id)->first();

        $order = SessionDocument::where('session_id', $request->session_id)->max('agenda_order');
        $order = $order ? $order + 1 : 1;

        if ($sessionDocument) {
            $sessionDocument->update([
                'session_id' => $request->session_id,
                'agenda_order' => $order,
            ]);
        } else {
            $sessionDocument = SessionDocument::create([
                'session_id' => $request->session_id,
                'document_id' => $document->id,
                'agenda_order' => $order,
            ]);
        }

        $document->update([
            'session_id' => $request->session_id,
            'agenda_order' => $sessionDocument->agenda_order,
        ]);

        return back()->with('success', 'Session meeting updated for this document.');
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'session_id' => 'required|exists:session_meetings,id',
        ]);

        $document = MemberDocument::findOrFail($id);
        $document->update(['status' => 'approved']);

        $order = SessionDocument::where('session_id', $request->session_id)->max('agenda_order');
        $order = $order ? $order + 1 : 1;

        SessionDocument::create([
            'session_id' => $request->session_id,
            'document_id' => $document->id,
            'agenda_order' => $order,
        ]);

        return back()->with('success', 'Document approved and added to session agenda.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string',
        ]);

        $document = MemberDocument::findOrFail($id);
        $document->update([
            'status' => 'rejected',
            'remarks' => $request->remarks,
        ]);

        return back()->with('success', 'Document rejected.');
    }

    public function view($id)
    {
        $document = MemberDocument::findOrFail($id);
        return view('secretary.documents.view', compact('document'));
    }

    public function download($id)
    {
        $document = MemberDocument::findOrFail($id);
        return Storage::disk('public')->download($document->file_path);
    }

    public function delete($id)
    {
        $document = MemberDocument::findOrFail($id);
        $document->delete();
        return back()->with('success', 'Document deleted successfully.');
    }

    public function secureAction(Request $request)
    {
        $request->validate([
            'document_id' => 'required|exists:member_documents,id',
            'action' => 'required|in:delete,forward,view',
            'password' => 'required',
        ]);
    }
}
