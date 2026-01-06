<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Handle admin login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            // Get the response
            $response = redirect()->intended(route('admin.dashboard'));
            
            // Manually set the session cookie with correct expiration (120 minutes from now)
            $sessionName = config('session.cookie');
            $sessionId = $request->session()->getId();
            $minutes = config('session.lifetime', 120);
            
            // Create cookie with correct expiration time (minutes * 60 to convert to seconds)
            $cookie = cookie(
                $sessionName,
                $sessionId,
                $minutes, // Laravel's cookie() helper expects minutes, not seconds
                config('session.path', '/'),
                config('session.domain', null),
                config('session.secure', false),
                config('session.http_only', true),
                false,
                config('session.same_site', 'lax')
            );
            
            return $response->withCookie($cookie);
        }

        throw ValidationException::withMessages([
            'email' => __('These credentials do not match our records.'),
        ]);
    }

    /**
     * Handle admin logout.
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}









