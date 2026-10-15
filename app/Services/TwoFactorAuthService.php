<?php

namespace App\Services;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthService
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA;
    }

    /**
     * Generate a new 2FA secret for the user.
     */
    public function generateSecret(User $user): array
    {
        $secret = $this->google2fa->generateSecretKey();
        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => null,
        ]);

        return [
            'secret' => $secret,
            'qr_code_url' => $this->getQrCodeUrl($user, $secret),
            'recovery_codes' => $recoveryCodes,
        ];
    }

    /**
     * Get the QR code URL for the user.
     */
    public function getQrCodeUrl(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeGoogleUrl(
            config('app.name'),
            $user->email,
            $secret
        );
    }

    /**
     * Generate recovery codes.
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8));
        }

        return $codes;
    }

    /**
     * Verify a 2FA code for the user.
     */
    public function verifyCode(User $user, string $code): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }

        $valid = $this->google2fa->verifyKey($user->two_factor_secret, $code);

        if ($valid) {
            if (! $user->two_factor_confirmed_at) {
                $user->update(['two_factor_confirmed_at' => now()]);
            }

            return true;
        }

        // Check recovery codes
        $recoveryCodes = $user->two_factor_recovery_codes ?? [];
        $code = strtoupper(str_replace(' ', '', $code));

        if (in_array($code, $recoveryCodes)) {
            $recoveryCodes = array_diff($recoveryCodes, [$code]);
            $user->update([
                'two_factor_recovery_codes' => array_values($recoveryCodes),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Disable 2FA for the user.
     */
    public function disable(User $user): void
    {
        $user->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ]);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(User $user): array
    {
        $recoveryCodes = $this->generateRecoveryCodes();
        $user->update(['two_factor_recovery_codes' => $recoveryCodes]);

        return $recoveryCodes;
    }

    /**
     * Check if user has 2FA enabled.
     */
    public function isEnabled(User $user): bool
    {
        return ! empty($user->two_factor_secret) && ! empty($user->two_factor_confirmed_at);
    }

    /**
     * Check if user has 2FA setup pending.
     */
    public function isSetupPending(User $user): bool
    {
        return ! empty($user->two_factor_secret) && empty($user->two_factor_confirmed_at);
    }
}
