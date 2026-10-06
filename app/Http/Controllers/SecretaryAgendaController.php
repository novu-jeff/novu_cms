<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SessionDocument;
use App\Models\SessionMeeting;
use Barryvdh\DomPDF\Facade\Pdf;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class SecretaryAgendaController extends Controller
{

    public function index($id)
    {
        $session = SessionMeeting::findOrFail($id);

        $documents = SessionDocument::where('session_id', $id)
            ->with('document.member')
            ->orderBy('agenda_order')
            ->get();

        return view('secretary.sessions.agenda', compact('session','documents','id'));
    }

    public function reorder(Request $request)
    {
        foreach ($request->order as $index => $docId) {

            SessionDocument::where('id', $docId)
                ->update([
                    'agenda_order' => $index + 1
                ]);
        }

        return response()->json(['success'=>true]);
    }

    public function packet($id)
    {
        $session = SessionMeeting::findOrFail($id);

        $documents = SessionDocument::where('session_id',$id)
            ->with('document.member')
            ->orderBy('agenda_order')
            ->get();

        $pdf = new Fpdi();

        /*
        ============================
        1. COVER PAGE
        ============================
        */

        $pdf->AddPage();

        // Add logo on the cover page if available
        $logoPath = public_path('default/logo_jones.png');
        if (file_exists($logoPath)) {
            // x = 10, y = 10, width = 25mm (height auto)
            $pdf->Image($logoPath, 10, 10, 25);
        }

        // Move cursor below the logo area
        $pdf->SetY(40);

        $pdf->SetFont('Arial','B',18);
        $pdf->Cell(0,20,'Municipality of Jones',0,1,'C');

        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,10,'Sangguniang Bayan',0,1,'C');

        $pdf->Ln(10);

        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,10,$session->title,0,1,'C');

        $pdf->SetFont('Arial','',12);
        $pdf->Cell(0,10,\Carbon\Carbon::parse($session->session_date)->format('F d, Y'),0,1,'C');

        $pdf->Ln(20);

        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,10,'AGENDA PACKET',0,1,'C');

        /*
        ============================
        2. TABLE OF CONTENTS
        ============================
        */

        $pdf->AddPage();

        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,10,'Agenda',0,1);

        $pdf->Ln(5);

        $pdf->SetFont('Arial','',12);

        foreach($documents as $doc){

            $title = $doc->document->title ?? 'Untitled';

            $member = $doc->document->member->name ?? 'Member';

            $pdf->Cell(0,8,
                $doc->agenda_order.'. '.$title.' ('.$member.')',
                0,1
            );
        }

        /*
        ============================
        3. MERGE DOCUMENT PDFS
        ============================
        */

        foreach ($documents as $doc) {

            $url = config('app.lis_storage_url').'/'.$doc->document->file_path;

            $tempDir = storage_path('app/temp');

            if(!file_exists($tempDir)){
                mkdir($tempDir,0777,true);
            }

            $tempFile = $tempDir.'/'.basename($doc->document->file_path);

            $response = Http::get($url);
            /*$path = storage_path('app/public/'.$doc->document->file_path);

            if (!file_exists($path)) {
                continue;
            }*/

            if(!$response->successful()){
                continue;
            }

            file_put_contents($tempFile,$response->body());

            if(!file_exists($tempFile)){
                continue;
            }

            $pageCount = $pdf->setSourceFile($tempFile);

            for ($i=1; $i <= $pageCount; $i++) {

                $template = $pdf->importPage($i);

                $size = $pdf->getTemplateSize($template);

                $pdf->AddPage(
                    $size['orientation'],
                    [$size['width'],$size['height']]
                );

                $pdf->useTemplate($template);
            }
        }

        /*
        ============================
        4. SAVE PACKET
        ============================
        */

        $fileName = 'session_'.now()->format('YmdHis').'_'.$session->id.'_packet.pdf';

        $path = storage_path('app/public/session-packets/'.$fileName);

        $pdf->Output($path,'F');

        $session->update([
            'packet_file' => 'session-packets/'.$fileName
        ]);

        return back()->with('success','Agenda packet generated.');
    }

    public function minutesTemplate($id)
    {
        $session = SessionMeeting::findOrFail($id);

        $documents = SessionDocument::where('session_id',$id)
            ->with('document.member')
            ->orderBy('agenda_order')
            ->get();

        $pdf = Pdf::loadView(
            'secretary.sessions.minutes-template',
            compact('session','documents')
        );

        return $pdf->download(
            'minutes_template_'.$session->id.'.pdf'
        );
    }

}
