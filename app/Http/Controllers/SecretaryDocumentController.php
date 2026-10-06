<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MemberDocument;
use App\Models\SessionDocument;
use App\Models\SessionMeeting;
use Illuminate\Support\Facades\Storage;

class SecretaryDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = MemberDocument::whereIn('status', ['forwarded', 'approved'])
            ->with(['member', 'sessionDocument.session'])
            ->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhereHas('member', function ($memberQuery) use ($search) {
                        $memberQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($status = $request->input('status')) {
            if (in_array($status, ['forwarded', 'approved'])) {
                $query->where('status', $status);
            }
        }

        $documents = $query->get();

        return view('secretary.documents.index', [
            'documents' => $documents,
            'search'    => $search ?? '',
            'status'    => $status ?? '',
        ]);
    }

    public function updateSession(Request $request, $id)
    {
        $request->validate([
            'session_id' => 'required|exists:session_meetings,id',
        ]);

        $document = MemberDocument::findOrFail($id);

        $sessionDocument = SessionDocument::where('document_id', $document->id)->first();

        // determine new agenda order for the selected session
        $order = SessionDocument::where('session_id', $request->session_id)
            ->max('agenda_order');
        $order = $order ? $order + 1 : 1;

        if ($sessionDocument) {
            $sessionDocument->update([
                'session_id'   => $request->session_id,
                'agenda_order' => $order,
            ]);
        } else {
            $sessionDocument = SessionDocument::create([
                'session_id'   => $request->session_id,
                'document_id'  => $document->id,
                'agenda_order' => $order,
            ]);
        }

        // keep mirrored fields on member_documents in sync if used
        $document->update([
            'session_id'   => $request->session_id,
            'agenda_order' => $sessionDocument->agenda_order,
        ]);

        return back()->with('success', 'Session meeting updated for this document.');
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'session_id' => 'required'
        ]);

        $document = MemberDocument::findOrFail($id);

        // update document status
        $document->update([
            'status' => 'approved'
        ]);

        // get next agenda order
        $order = SessionDocument::where('session_id', $request->session_id)
            ->max('agenda_order');

        $order = $order ? $order + 1 : 1;

        // insert into session_documents
        SessionDocument::create([
            'session_id'   => $request->session_id,
            'document_id'  => $document->id,
            'agenda_order' => $order
        ]);

        return back()->with('success','Document approved and added to session agenda.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required'
        ]);

        $document = MemberDocument::findOrFail($id);

        $document->update([
            'status' => 'rejected',
            'remarks' => $request->remarks
        ]);

        return back()->with('success','Document rejected.');
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

        return back()->with('success','Document deleted successfully.');
    }

    public function secureAction(Request $request)
    {
        $request->validate([
            'document_id' => 'required|exists:member_documents,id',
            'action' => 'required|in:delete,forward,view',
            'password' => 'required'
        ]);
    }

    

}
