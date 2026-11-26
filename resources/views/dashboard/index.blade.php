@extends('layouts.app')

@section('styles')
<style>
    .cms-dashboard { --cms-border:#e8edf3; --cms-muted:#667085; }
    .cms-welcome { border:1px solid var(--cms-border); border-radius:18px; padding:1.5rem; background:linear-gradient(120deg,#f7faff 0%,#fff 70%); }
    .cms-kicker { color:#667085; font-size:.75rem; font-weight:700; letter-spacing:.09em; text-transform:uppercase; }
    .cms-stat { height:100%; border:1px solid var(--cms-border); border-radius:16px; padding:1.1rem; background:#fff; transition:transform .15s ease,box-shadow .15s ease; }
    .cms-stat:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(16,24,40,.07); }
    .cms-stat-icon { display:inline-flex; width:44px; height:44px; align-items:center; justify-content:center; border-radius:13px; font-size:1.1rem; }
    .cms-stat-label { color:var(--cms-muted); font-size:.86rem; }
    .cms-stat-number { color:#101828; font-size:1.85rem; line-height:1.2; font-weight:750; margin-top:.45rem; }
    .cms-section { font-size:1rem; font-weight:700; color:#101828; }
    .cms-shortcut { display:flex; align-items:center; gap:.85rem; height:100%; border:1px solid var(--cms-border); border-radius:13px; padding:1rem; color:inherit; text-decoration:none; background:#fff; transition:all .15s ease; }
    .cms-shortcut:hover { border-color:#b8cffb; background:#f8fbff; color:#0d6efd; transform:translateY(-1px); }
    .cms-shortcut-icon { width:40px; height:40px; flex:0 0 40px; border-radius:11px; display:flex; align-items:center; justify-content:center; background:#f2f4f7; color:#344054; }
    .cms-shortcut-title { font-weight:650; font-size:.92rem; }
    .cms-shortcut-desc { color:var(--cms-muted); font-size:.78rem; margin-top:.15rem; }
    @media(max-width:575.98px) { .cms-welcome { padding:1.15rem; } .cms-stat-number { font-size:1.55rem; } }
</style>
@endsection

@section('content')
<div class="container-fluid py-3 cms-dashboard">
    <div class="cms-welcome mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="cms-kicker mb-2">Content Management System</div>
            <h3 class="fw-bold mb-1">Admin Dashboard</h3>
            <p class="text-muted mb-0">Overview of your municipal website content and administration.</p>
        </div>
        <div class="text-muted small"><i class="fa-regular fa-calendar me-2"></i>{{ now()->format('l, F j, Y') }}</div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="cms-section mb-0">Content Overview</h5>
        <span class="small text-muted">Current record totals</span>
    </div>
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label'=>'Members','value'=>$stats['members'],'icon'=>'fa-users','tone'=>'#eaf2ff','color'=>'#2563eb','route'=>'members.index'],
                ['label'=>'Barangays','value'=>$stats['barangays'],'icon'=>'fa-map-location-dot','tone'=>'#e8f8ef','color'=>'#16834a','route'=>'barangays.index'],
                ['label'=>'Barangay Officials','value'=>$stats['barangay_officials'],'icon'=>'fa-user-tie','tone'=>'#f1eaff','color'=>'#7c3aed','route'=>'barangay-officials.index'],
                ['label'=>'Calendar Events','value'=>$stats['events'],'icon'=>'fa-calendar-days','tone'=>'#fff2df','color'=>'#c26a08','route'=>'calendar-event.index'],
                ['label'=>'Photo Journals','value'=>$stats['photo_journals'],'icon'=>'fa-images','tone'=>'#ffe9ed','color'=>'#c0264d','route'=>'photo-journals.index'],
                ['label'=>'Standing Committees','value'=>$stats['committees'],'icon'=>'fa-people-group','tone'=>'#e6f7f8','color'=>'#087e8b','route'=>'standing-committee.index'],
                ['label'=>'District Assignments','value'=>$stats['district_assignments'],'icon'=>'fa-map','tone'=>'#f0f2f5','color'=>'#475467','route'=>'district-assignments.index'],
                ['label'=>'Organization Units','value'=>$stats['organization_units'],'icon'=>'fa-sitemap','tone'=>'#fff0e6','color'=>'#b54708','route'=>'organization.index'],
            ];
        @endphp
        @foreach($cards as $card)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="cms-stat">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="cms-stat-label">{{ $card['label'] }}</div>
                        <span class="cms-stat-icon" style="background:{{ $card['tone'] }};color:{{ $card['color'] }}"><i class="fa-solid {{ $card['icon'] }}"></i></span>
                    </div>
                    <div class="cms-stat-number">{{ number_format($card['value']) }}</div>
                    <a href="{{ route($card['route']) }}" class="small text-decoration-none mt-3 d-inline-flex align-items-center gap-2">Manage <i class="fa-solid fa-arrow-right-long"></i></a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="cms-section mb-0">Quick Actions</h5>
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <a class="cms-shortcut" href="{{ route('calendar-event.index') }}"><span class="cms-shortcut-icon"><i class="fa-regular fa-calendar-plus"></i></span><span><span class="cms-shortcut-title d-block">Manage Calendar Events</span><span class="cms-shortcut-desc d-block">Create and update upcoming events.</span></span><i class="fa-solid fa-chevron-right ms-auto small"></i></a>
                </div>
                <div class="col-12 col-md-6">
                    <a class="cms-shortcut" href="{{ route('photo-journals.index') }}"><span class="cms-shortcut-icon"><i class="fa-regular fa-images"></i></span><span><span class="cms-shortcut-title d-block">Manage Photo Journals</span><span class="cms-shortcut-desc d-block">Organize albums and photo coverage.</span></span><i class="fa-solid fa-chevron-right ms-auto small"></i></a>
                </div>
                <div class="col-12 col-md-6">
                    <a class="cms-shortcut" href="{{ route('barangays.index') }}"><span class="cms-shortcut-icon"><i class="fa-solid fa-map-location-dot"></i></span><span><span class="cms-shortcut-title d-block">Barangay Settings</span><span class="cms-shortcut-desc d-block">Maintain the barangay directory.</span></span><i class="fa-solid fa-chevron-right ms-auto small"></i></a>
                </div>
                <div class="col-12 col-md-6">
                    <a class="cms-shortcut" href="{{ route('settings.organization.edit') }}"><span class="cms-shortcut-icon"><i class="fa-solid fa-sliders"></i></span><span><span class="cms-shortcut-title d-block">Organization Settings</span><span class="cms-shortcut-desc d-block">Update municipal organization details.</span></span><i class="fa-solid fa-chevron-right ms-auto small"></i></a>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <h5 class="cms-section mb-3">Administration</h5>
            <div class="cms-stat">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="cms-stat-icon" style="background:#eef2ff;color:#4338ca"><i class="fa-solid fa-user-shield"></i></span>
                    <div><div class="fw-semibold">CMS User Accounts</div><div class="small text-muted">Manage access to the CMS.</div></div>
                </div>
                <div class="d-flex align-items-end justify-content-between">
                    <div><div class="cms-stat-number mt-0">{{ number_format($stats['users']) }}</div><div class="small text-muted">Total accounts</div></div>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-primary btn-sm">Manage Users <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
            </div>
            <div class="small text-muted mt-3"><i class="fa-solid fa-circle-info me-1"></i>Counts reflect records currently stored in the database.</div>
        </div>
    </div>
</div>
@endsection
