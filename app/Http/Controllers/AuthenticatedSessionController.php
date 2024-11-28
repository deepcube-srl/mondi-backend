<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Socialite\Facades\Socialite;

class AuthenticatedSessionController extends Controller
{
    public function logout(
        Request $request
    ) {

        $spaLoginUrl = config('spa.base_url').'/login';
        $user = Auth::user();

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if (isset($user->provider) && $user->provider === 'azure') {
            $azureLogoutUrl = Socialite::driver($user->provider)->getLogoutUrl($spaLoginUrl);
            return redirect($azureLogoutUrl);
        }

        return app(LogoutResponse::class);
    }
}
