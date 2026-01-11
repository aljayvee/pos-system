<?php

namespace App\Services\Otp;

use Illuminate\Support\Facades\Cache;

class CacheOtpService implements OtpServiceInterface
{
    protected const EXPIRY_MINUTES = 10;

    public function generate(string $identifier, string $context): string
    {
        $code = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $key = $this->getKey($identifier, $context);

        // Store in cache for 10 minutes
        Cache::put($key, $code, now()->addMinutes(self::EXPIRY_MINUTES));

        return $code;
    }

    public function validate(string $identifier, string $code, string $context): bool
    {
        $key = $this->getKey($identifier, $context);
        $cachedCode = Cache::get($key);

        if (!$cachedCode) {
            return false;
        }

        if ($cachedCode === $code) {
            Cache::forget($key); // Burn the code after use
            return true;
        }

        return false;
    }

    protected function getKey(string $identifier, string $context): string
    {
        // Example: otp_password_reset_user@example.com
        return "otp_{$context}_{$identifier}";
    }
}
