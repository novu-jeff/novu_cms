<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrganizationSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationSettingsApiController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $expectedToken = (string) config('services.organization_settings.token');
        $providedToken = (string) $request->bearerToken();

        if (
            $expectedToken === '' ||
            $providedToken === '' ||
            ! hash_equals($expectedToken, $providedToken)
        ) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $organization = OrganizationSetting::query()->first();

        if (! $organization) {
            return response()->json([
                'message' => 'Organization settings have not been configured.',
            ], 404);
        }

        $logoUrl = $organization->logo_path
            ? Storage::disk('public')->url($organization->logo_path)
            : null;

        return response()->json([
            'organization_name' => $organization->organization_name,
            'organization_subtitle' => $organization->organization_subtitle,
            'logo_url' => $logoUrl,
            'address' => $organization->address,
            'contact_information' => $organization->contact_information,
        ]);
    }
}
