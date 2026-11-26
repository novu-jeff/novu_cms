<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MembersAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MemberAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $account = MembersAccount::where(
            'email',
            $credentials['email']
        )->first();

        if (
            !$account ||
            !$account->is_active ||
            !Hash::check($credentials['password'], $account->password)
        ) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials or inactive account.'],
            ]);
        }

        $token = $account->createToken(
            'lis-voting',
            ['vote:cast']
        )->plainTextToken;

        return response()->json([
            'message' => 'Authenticated successfully.',
            'token' => $token,
            'token_type' => 'Bearer',
            'member' => [
                'account_id' => $account->id,
                'member_id' => $account->member_id,
                'name' => optional($account->member)->name,
                'email' => $account->email,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
