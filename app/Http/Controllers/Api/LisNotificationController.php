<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\LisDocumentSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LisNotificationController extends Controller
{
    public function documentSubmitted(Request $request)
    {
        Log::info('LIS notification API reached.', [
            'document_id' => $request->input('document_id'),
        ]);

        $validated = $request->validate([
            'document_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'member_name' => 'required|string|max:255',
            'document_url' => 'required|url',
        ]);

        Log::info('LIS notification request validated.');

        $secretary = User::whereHas('role', function ($query) {
            $query->where('name', 'secretary');
        })->first();

        Log::info('LIS Secretary lookup result.', [
            'secretary_id' => $secretary?->id,
            'secretary_name' => $secretary?->name,
        ]);

        if (!$secretary) {
            return response()->json([
                'success' => false,
                'message' => 'Secretary account not found.',
            ], 404);
        }

        Log::info('Sending database notification.');

        $secretary->notify(
            new LisDocumentSubmitted($validated)
        );

        Log::info('LIS database notification saved.', [
            'secretary_id' => $secretary->id,
            'document_id' => $validated['document_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Secretary notified.',
        ]);
    }
}
