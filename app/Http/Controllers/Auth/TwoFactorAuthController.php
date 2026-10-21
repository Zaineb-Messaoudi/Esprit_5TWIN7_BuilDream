<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorAuthController extends Controller
{
    public function __construct(
        protected TwoFactorAuthService $twoFactorAuthService
    ) {}

    /**
     * Show the 2FA setup page with QR code.
     */
    public function setup(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($this->twoFactorAuthService->isEnabled($user)) {
            return redirect()->route('profile.edit')
                ->with('status', '2fa-already-enabled');
        }

        if ($this->twoFactorAuthService->isSetupPending($user)) {
            $qrCodeUrl = $user->two_factor_secret
                ? $this->twoFactorAuthService->getQrCodeUrl($user, $user->two_factor_secret)
                : null;

            return view('auth.two-factor.setup', [
                'qrCodeUrl' => $qrCodeUrl,
                'recoveryCodes' => $user->two_factor_recovery_codes ?? [],
            ]);
        }

        $data = $this->twoFactorAuthService->generateSecret($user);

        return view('auth.two-factor.setup', [
            'qrCodeUrl' => $data['qr_code_url'],
            'recoveryCodes' => $data['recovery_codes'],
        ]);
    }

    /**
     * Enable 2FA for the user.
     */
    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        $code = $request->string('code');

        if ($this->twoFactorAuthService->verifyCode($user, $code)) {
            return redirect()->route('profile.edit')
                ->with('status', '2fa-enabled');
        }

        return back()->withErrors([
            'code' => 'The provided code is invalid.',
        ]);
    }

    /**
     * Show the 2FA verification page.
     */
    public function verify(Request $request): View
    {
        return view('auth.two-factor.verify');
    }

    /**
     * Verify the 2FA code.
     */
    public function verifyCode(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();
        $code = $request->string('code');

        if ($this->twoFactorAuthService->verifyCode($user, $code)) {
            $request->session()->put('2fa_verified', true);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'code' => 'The provided code is invalid.',
        ]);
    }

    /**
     * Disable 2FA for the user.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $this->twoFactorAuthService->disable($request->user());

        return redirect()->route('profile.edit')
            ->with('status', '2fa-disabled');
    }

    /**
     * Show the recovery codes.
     */
    public function recoveryCodes(Request $request): View
    {
        $user = $request->user();
        $recoveryCodes = $user->two_factor_recovery_codes ?? [];

        return view('auth.two-factor.recovery-codes', [
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $this->twoFactorAuthService->isEnabled($user)) {
            return redirect()->route('profile.edit')
                ->with('error', '2fa-not-enabled');
        }

        $recoveryCodes = $this->twoFactorAuthService->regenerateRecoveryCodes($user);

        return view('auth.two-factor.recovery-codes', [
            'recoveryCodes' => $recoveryCodes,
            'regenerated' => true,
        ]);
    }
}
