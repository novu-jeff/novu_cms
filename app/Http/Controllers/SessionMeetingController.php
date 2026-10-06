<?php

namespace App\Http\Controllers;

use App\Models\SessionMeeting;
use Illuminate\Http\Request;

class SessionMeetingController extends Controller
{

    public function index()
    {
        $sessions = SessionMeeting::withCount('documents')->latest()->get();
        return view('secretary.session-meetings.index', compact('sessions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'session_date' => 'required|date'
        ]);

        SessionMeeting::create([
            'title' => $request->title,
            'session_date' => $request->session_date,
            'status' => 'upcoming'
        ]);

        return back()->with('success','Session created.');
    }

    public function edit(SessionMeeting $session_meeting)
    {
        return view('secretary.session-meetings.edit', compact('session_meeting'));
    }

    public function update(Request $request, SessionMeeting $session_meeting)
    {
        $request->validate([
            'title' => 'required',
            'session_date' => 'required|date',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled',
        ]);

        $session_meeting->update([
            'title' => $request->title,
            'session_date' => $request->session_date,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('session-meetings.index')
            ->with('success', 'Session updated.');
    }

    public function destroy(SessionMeeting $session_meeting)
    {
        if ($session_meeting->documents()->exists()) {
            return back()->with('error', 'Cannot delete: this session has documents. Remove all documents first.');
        }

        $session_meeting->delete();
        return redirect()
            ->route('session-meetings.index')
            ->with('success', 'Session deleted.');
    }
}