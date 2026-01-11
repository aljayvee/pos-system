<?php

namespace App\Services\Otp;

interface OtpServiceInterface
{
    /**
     * Generate a new OTP for a specific context.
     *
     * @param string $identifier Unique identifier (e.g., user_email, user_id)
     * @param string $context Purpose (e.g., 'password_reset', 'mpin_reset', 'email_verify')
     * @return string The generated 6-digit code
     */
    public function generate(string $identifier, string $context): string;

    /**
     * Validate an OTP.
     *
     * @param string $identifier Unique identifier
     * @param string $code The code provided by user
     * @param string $context Purpose
     * @return bool True if valid
     */
    public function validate(string $identifier, string $code, string $context): bool;
}
