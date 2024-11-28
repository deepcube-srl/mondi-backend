<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class HandleSocialSessions
{
    /**
     * Handle an incoming request.
     * If the logged user used a social provider to authenticate,
     * check through the token if authentication is still valid.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && !isset($user->password)) {
            if (isset($user->provider) && in_array($user->provider, ['azure'])) {
                try {
                    Socialite::driver($user->provider)
                        ->userFromToken($request->user()->provider_token);
                } catch (ClientException $clientException) {
                    try {
                        if (isset($user->provider_refresh_token)) {
                            // has not refresh token, google doesn't give that back
                            $token = Socialite::driver($user->provider)
                                ->refreshToken($user->provider_refresh_token);

                            Log::info('Obtained new token from refresh token.');
                            $user->update([
                                'provider_token' => $token->token,
                                'provider_refresh_token' => $token->refreshToken,
                            ]);
                        } else {
                            Log::info('Refresh token not found for user...');
                            throw $clientException;
                        }
                    } catch (\Throwable $throwable) {
                        Log::info('Logging out user... '.$throwable->getMessage());
                        Auth::guard('web')->logout();
                        throw new AuthenticationException();
                    }
                }
            }
        }

        return $next($request);
    }
}
