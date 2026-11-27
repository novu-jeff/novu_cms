<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SecretaryNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(20);

        return view('secretary.notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return redirect()->away(
            $notification->data['document_url']
        );
    }
}