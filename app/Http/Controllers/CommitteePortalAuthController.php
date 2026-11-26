<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommitteePortalAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('member')->check()) {
            return redirect()->route('committee-portal.dashboard');
        }

        return view('committee-portal.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::guard('member')->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true,
        ], $request->boolean('remember'))) {
            return back()
                ->withErrors([
                    'email' => 'Invalid credentials or inactive account.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(
            route('committee-portal.dashboard')
        );
    }

    public function logout(Request $request)
    {
        Auth::guard('member')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('committee-portal.login');
    }
}