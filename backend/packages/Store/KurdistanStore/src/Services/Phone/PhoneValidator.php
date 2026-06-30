<?php

namespace Store\KurdistanStore\Services\Phone;

class PhoneValidator
{
    /**
     * Check whether a (already-normalised) E.164 phone number satisfies the
     * configured country restriction.
     *
     * The pattern is intentionally stored in config so it can be widened or
     * replaced without touching code.
     */
    public function isValid(string $e164Phone): bool
    {
        $pattern = config('kurdistan-store.phone.e164_pattern', '^\+9647\d{9}$');

        return (bool) preg_match('/' . $pattern . '/', $e164Phone);
    }

    /** Return a human-readable validation message. */
    public function message(): string
    {
        return 'Please enter a valid Iraqi mobile number (e.g. 0750 123 4567).';
    }
}
