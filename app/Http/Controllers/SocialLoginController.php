<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SocialLoginController extends Controller
{
    private static bool $registrationEnabled = true;

    public function redirectToProvider(string $provider): RedirectResponse {
        return Socialite::driver($provider)
            ->setScopes(['User.read', 'offline_access'])
            ->redirect();
    }

    public function callback(string $provider, Request $request): \Illuminate\Http\RedirectResponse
    {

        $socialUser = Socialite::driver($provider)->user();
        if (!$socialUser) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        $user = User::firstWhere('email', $socialUser->email);

        if (isset($user)) {
            if (!isset($user->profile_photo_path) && ($avatar = $socialUser->getAvatar())) {
                $profilePhotoPath = $this->storeAvatar($avatar, $socialUser->getId());
            }
            $user->update(array_merge(
                [
                    'provider' => $provider,
                    'provider_id' => $socialUser->id,
                    'provider_token' => $socialUser->token,
                    'provider_refresh_token' => $socialUser->refreshToken,
                ],
                isset($profilePhotoPath) ? ['profile_photo_path' => $profilePhotoPath] : []
            ));
        } elseif (self::$registrationEnabled) {
            if ($avatar = $socialUser->getAvatar()) {
                $profilePhotoPath = $this->storeAvatar($avatar, $socialUser->getId());
            }
            $user = User::create(
                array_merge(
                    [
                        'email' => $socialUser->email,
                        'name' => $socialUser->user['givenName'],
                        'surname' => $socialUser->user['surname'],
                        'provider' => $provider,
                        'provider_id' => $socialUser->id,
                        'provider_token' => $socialUser->token,
                        'provider_refresh_token' => $socialUser->refreshToken,
                    ],
                    isset($profilePhotoPath) ? ['profile_photo_path' => $profilePhotoPath] : []
                ));

            event(new Registered($user));
        } else {
            throw ValidationException::withMessages([
                'microsoft-callback' => [trans('auth.registration_not_enabled')],
            ])
                ->redirectTo(config('spa.base_url'));
        }

        if (isset($user)) {
            Auth::guard('web')->login($user);
            //$request->session()->regenerate();

            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }
        }

        return redirect()->away(config('spa.base_url') . '/');
    }

    private function storeAvatar($avatar, $userId): false|string
    {
        try {

            $data = $avatar->getContents();
            $ext = explode('/', $avatar->getContentType())[1] ?? 'png';
            $publicDiskPath = "social-photos/$userId.$ext";
            if (Storage::disk('public')->put($publicDiskPath, $data)) {
                return $publicDiskPath;
            }

            return false;
        } catch (\Exception $e) {
            Log::error(__METHOD__.' got an exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        return false;
    }
}
