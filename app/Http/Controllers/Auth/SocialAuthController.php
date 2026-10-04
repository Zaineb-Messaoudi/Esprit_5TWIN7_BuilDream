<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    public function redirect(Request $request, string $provider): RedirectResponse
    {
        $this->providerConfig($provider);
        $state = Str::random(40);
        $request->session()->put('social_auth', [
            'provider' => $provider,
            'state' => $state,
        ]);

        $config = $this->providerConfig($provider);
        $parameters = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect'],
            'response_type' => 'code',
            'state' => $state,
        ];

        if ($provider === 'google') {
            $parameters['scope'] = 'openid email profile';
            $parameters['prompt'] = 'select_account';
            $url = 'https://accounts.google.com/o/oauth2/v2/auth';
        } else {
            $parameters['scope'] = 'email,public_profile';
            $url = 'https://www.facebook.com/'.$config['graph_version'].'/dialog/oauth';
        }

        return redirect()->away($url.'?'.http_build_query($parameters, '', '&', PHP_QUERY_RFC3986));
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        $authorization = $request->session()->pull('social_auth');

        if (
            ! is_array($authorization)
            || ($authorization['provider'] ?? null) !== $provider
            || ! hash_equals((string) ($authorization['state'] ?? ''), (string) $request->query('state', ''))
        ) {
            return $this->loginError(__('Social sign-in could not be verified. Please try again.'));
        }

        if ($request->filled('error') || ! $request->filled('code')) {
            return $this->loginError(__('Social sign-in was cancelled or denied.'));
        }

        $config = $this->providerConfig($provider);
        try {
            $tokenResponse = $provider === 'google'
                ? Http::asForm()->acceptJson()->timeout(10)->post('https://oauth2.googleapis.com/token', [
                    'code' => $request->query('code'),
                    'client_id' => $config['client_id'],
                    'client_secret' => $config['client_secret'],
                    'redirect_uri' => $config['redirect'],
                    'grant_type' => 'authorization_code',
                ])
                : Http::acceptJson()->timeout(10)->get('https://graph.facebook.com/'.$config['graph_version'].'/oauth/access_token', [
                    'code' => $request->query('code'),
                    'client_id' => $config['client_id'],
                    'client_secret' => $config['client_secret'],
                    'redirect_uri' => $config['redirect'],
                ]);

            if (! $tokenResponse->successful() || ! is_string($token = $tokenResponse->json('access_token'))) {
                Log::warning('Social OAuth token exchange failed.', ['provider' => $provider, 'status' => $tokenResponse->status()]);

                return $this->loginError(__('The identity provider could not complete sign-in. Please try again.'));
            }

            $profileResponse = $provider === 'google'
                ? Http::acceptJson()->withToken($token)->timeout(10)->get('https://openidconnect.googleapis.com/v1/userinfo')
                : Http::acceptJson()->withToken($token)->timeout(10)->get('https://graph.facebook.com/me', [
                    'fields' => 'id,name,email',
                ]);
        } catch (ConnectionException) {
            Log::warning('Social OAuth provider was unreachable.', ['provider' => $provider]);

            return $this->loginError(__('The identity provider is temporarily unavailable. Please try again.'));
        }

        $profile = $profileResponse->json();
        $providerId = is_array($profile) ? ($profile['sub'] ?? $profile['id'] ?? null) : null;
        $email = is_array($profile) ? ($profile['email'] ?? null) : null;
        $name = is_array($profile) ? ($profile['name'] ?? null) : null;
        $verified = $provider === 'facebook' || ($profile['email_verified'] ?? false) === true;

        if (
            ! $profileResponse->successful()
            || ! is_string($providerId)
            || ! is_string($email)
            || ! filter_var($email, FILTER_VALIDATE_EMAIL)
            || ! is_string($name)
            || trim($name) === ''
            || ! $verified
        ) {
            Log::warning('Social OAuth profile was incomplete or unverified.', ['provider' => $provider, 'status' => $profileResponse->status()]);

            return $this->loginError(__('The provider did not return a verified email address. Use email sign-up instead.'));
        }

        $user = DB::transaction(function () use ($provider, $providerId, $email, $name): ?User {
            $providerColumn = $provider.'_id';
            $user = User::query()->where($providerColumn, $providerId)->lockForUpdate()->first();

            if (! $user) {
                $user = User::query()->where('email', Str::lower($email))->lockForUpdate()->first();

                if ($user && $user->{$providerColumn} && $user->{$providerColumn} !== $providerId) {
                    return null;
                }

                $user ??= new User([
                    'name' => trim($name),
                    'email' => Str::lower($email),
                    'role' => UserRole::BUYER,
                    'role_setup_completed' => false,
                    'password' => Str::random(64),
                ]);
                if (! $user->exists) {
                    $user->forceFill(['email_verified_at' => now()]);
                }
                $user->{$providerColumn} = $providerId;
                $user->save();
            }

            $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();

            return $user;
        });

        if (! $user) {
            return $this->loginError(__('This email is already linked to a different social account.'));
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $user->role_setup_completed
            ? redirect()->route('dashboard')
            : redirect()->route('role.setup');
    }

    private function providerConfig(string $provider): array
    {
        abort_unless(in_array($provider, ['google', 'facebook'], true), 404);

        $config = config('services.'.$provider);

        abort_if(
            empty($config['client_id']) || empty($config['client_secret']) || empty($config['redirect']),
            503,
            __('Social sign-in is not configured.')
        );

        return $config;
    }

    private function loginError(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['social' => $message]);
    }
}
