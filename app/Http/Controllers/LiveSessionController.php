<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LiveSession;

class LiveSessionController extends Controller
{
    public function editLive()
    {
        $url = LiveSession::get('live_session_url', '');
        return view('secretary.settings.live-session', compact('url'));
    }

    public function updateLive(Request $request)
    {
        $data = $request->validate([
            'live_session_url' => [ 'url', 'nullable'],
        ]);

        LiveSession::set('live_session_url', $data['live_session_url']);

        return back()->with('success', 'Live session URL updated.');
    }
}