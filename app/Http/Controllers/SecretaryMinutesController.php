<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\SessionMeeting;
use App\Models\SessionDocument;
use App\Models\SessionMinute;
use Illuminate\Support\Facades\Log;

class SecretaryMinutesController extends Controller
{
    public function index($id)
    {
        $session = SessionMeeting::findOrFail($id);

        $documents = SessionDocument::where('session_id',$id)
            ->with('document.member')
            ->orderBy('agenda_order')
            ->get();

        $minutes = SessionMinute::where('session_id',$id)
            ->get()
            ->keyBy('document_id');
    
        return view(
            'secretary.sessions.minutes',
            compact('session','documents','minutes')
        );    

       
    }

    public function store(Request $request,$id)
    {
        
        $session = SessionMeeting::findOrFail($id);

        $session->update([
            'call_to_order' => $request->call_to_order,
            'prayer' => $request->prayer,
            'roll_call' => $request->roll_call,
            'adjournment_time' => $request->adjournment_time,
            
        ]);

        foreach($request->minutes as $docId => $data){

            SessionMinute::updateOrCreate(
                [
                    'session_id'=>$id,
                    'document_id'=>$docId
                ],
                [
                    'discussion'=>$data['discussion'],
                    'motion'=>$data['motion'],
                    'decision'=>$data['decision']
                ]
            );
        }

        return back()->with('success','Minutes encoded successfully.');
    }

    public function review($id)
    {
        $session = SessionMeeting::findOrFail($id);

        $documents = SessionDocument::where('session_id',$id)
            ->with('document.member')
            ->orderBy('agenda_order')
            ->get();

        $minutes = SessionMinute::where('session_id',$id)
            ->get()
            ->keyBy('document_id');

        return view(
            'secretary.sessions.minutes-review',
            compact('session','documents','minutes')
        );
    }

    public function generate($id)
    {
        $session = SessionMeeting::findOrFail($id);

        $documents = SessionDocument::where('session_id',$id)
            ->with('document.member')
            ->orderBy('agenda_order')
            ->get();

        $minutes = SessionMinute::where('session_id',$id)->get()
            ->keyBy('document_id');

        Log::info('Generating minutes PDF for session: '.$session->id,[
            'session_id' => $session->id,
            'session_title' => $session->title,
            'documents_count' => $documents->count(),
            'minutes_count' => $minutes->count(),
        ]);    

        $pdf = Pdf::loadView(
            'secretary.sessions.minutes-pdf',
            compact('session','documents','minutes')
        );

        $session->update([
            'status' => 'completed'
        ]);

        return $pdf->download(
            'minutes_session_'.$session->title.'_'.$session->session_date.'.pdf'
        );
    }
}
