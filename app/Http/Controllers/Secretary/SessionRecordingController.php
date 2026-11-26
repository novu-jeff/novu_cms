<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessSessionRecording;
use App\Models\SessionRecording;
use App\Models\SessionMeeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

class SessionRecordingController extends Controller
{

    public function index(SessionMeeting $session)
    {
        // TODO: Apply your existing secretary/session authorization policy.

        $recordings = $session->recordings()
            ->with('transcript')
            ->latest()
            ->get();

        return view('secretary.sessions.recordings', compact(
            'session',
            'recordings'
        ));
    }

    public function store(Request $request, SessionMeeting $session)
    {
        // Add your application's secretary/session authorization
        // policy here before allowing uploads.

        abort_unless(
            config('services.assemblyai.api_key'),
            503,
            'Transcription service is not configured.'
        );

        $validated = $request->validate([
            'recording' => [
                'required',
                File::types([
                    'mp3', 'wav', 'm4a', 'mp4',
                    'webm', 'mpeg', 'mpga', 'ogg', 'flac',
                ])->max('500mb'),
            ],
        ]);

        $file = $validated['recording'];

        $disk = 'local';
        $directory = "session-recordings/{$session->id}";
        $filename = Str::uuid() . '.' . $file->extension();

        $path = $file->storeAs($directory, $filename, $disk);

        if (! $path) {
            abort(500, 'Unable to save recording.');
        }

        try {
            $recording = $session->recordings()->create([
                'uploaded_by' => $request->user()?->id,
                'original_filename' => $file->getClientOriginalName(),
                'disk' => $disk,
                'file_path' => $path,
                'mime_type' => $file->getMimeType()
                    ?? 'application/octet-stream',
                'file_size' => $file->getSize(),
                'status' => 'uploaded',
            ]);

            ProcessSessionRecording::dispatch($recording->id);
        } catch (\Throwable $e) {
            Storage::disk($disk)->delete($path);

            throw $e;
        }

        return back()->with(
            'success',
            'Recording uploaded. Transcription has been queued.'
        );
    }

    public function transcript(
        SessionMeeting $session,
        SessionRecording $recording
    ) {
        // TODO: Apply your existing secretary/session authorization policy.

        abort_unless(
            (int) $recording->session_id === (int) $session->id,
            404
        );

        $transcript = $recording->transcript;

        abort_unless(
            $transcript && $transcript->status === 'completed',
            404
        );

        return view(
            'secretary.sessions.transcript',
            compact('session', 'recording', 'transcript')
        );
    }
}