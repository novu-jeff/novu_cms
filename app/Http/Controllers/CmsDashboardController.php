<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Assignment;
use App\Models\Barangay;
use App\Models\BarangayOfficial;
use App\Models\CalendarEvent;
use App\Models\Committee;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CmsDashboardController extends Controller
{
    public function index(): View
    {
        // Counts only; no records or columns beyond each model's table are assumed.
        $stats = [
            'members' => Member::count(),
            'barangays' => Barangay::count(),
            'barangay_officials' => BarangayOfficial::count(),
            'events' => CalendarEvent::count(),
            'photo_journals' => Album::count(),
            'committees' => Committee::count(),
            'district_assignments' => Assignment::count(),
            'organization_units' => Organization::count(),
            'users' => Schema::hasTable('users') ? User::count() : 0,
        ];

        return view('dashboard.index', compact('stats'));
    }
}
