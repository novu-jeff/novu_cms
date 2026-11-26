<?php

namespace App\Http\Controllers;

use App\Models\OrganizationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationSettingController extends Controller
{
    public function edit()
    {
        $settings = OrganizationSetting::first();

        return view('settings.organization', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'organization_subtitle' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_information' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $settings = OrganizationSetting::first()
            ?? new OrganizationSetting();

        $settings->organization_name = $validated['organization_name'];
        $settings->organization_subtitle =
            $validated['organization_subtitle'] ?? null;
        $settings->address = $validated['address'] ?? null;
        $settings->contact_information =
            $validated['contact_information'] ?? null;

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            $settings->logo_path = $request->file('logo')
                ->store('organization-logos', 'public');
        }

        $settings->save();

        return redirect()
            ->route('settings.organization.edit')
            ->with('success', 'Organization settings updated successfully.');
    }
}