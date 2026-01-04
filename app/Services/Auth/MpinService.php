<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MpinService
{
    /**
     * Verify if the provided PIN matches the user's stored MPIN.
     *
     * @param User $user
     * @param string $pin
     * @return bool
     */
    public function verifyMpin(User $user, string $pin): bool
    {
        if (!$user->mpin) {
            return false;
        }

        return Hash::check($pin, $user->mpin->mpin);
    }

    /**
     * Set or update the user's MPIN.
     *
     * @param User $user
     * @param string $pin
     * @return void
     */
    public function setMpin(User $user, string $pin): void
    {
        $user->mpin()->updateOrCreate(
            ['user_id' => $user->id],
            ['mpin' => Hash::make($pin)]
        );

        Log::info("MPIN updated for user ID: {$user->id}");
    }

    /**
     * Check if the user has an MPIN configured.
     *
     * @param User $user
     * @return bool
     */
    public function hasMpin(User $user): bool
    {
        return $user->mpin()->exists();
    }
}
