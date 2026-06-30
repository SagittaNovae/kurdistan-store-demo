<?php

namespace Store\KurdistanStore\Services\Phone;

class PhoneNormalizer
{
    public function __construct(
        private readonly string $defaultCountryCode = '+964',
    ) {}

    /**
     * Normalize a phone number string to E.164 format.
     *
     * Handles:
     *   0750 123 4567  → +9647501234567
     *   +964 750…      → +964750…
     *   00964 750…     → +964750…
     */
    public function normalize(string $phone): string
    {
        // Strip everything except digits and a leading +
        $cleaned = preg_replace('/[^\d+]/', '', trim($phone));

        if ($cleaned === '') {
            return $phone;
        }

        // Already E.164
        if (str_starts_with($cleaned, '+')) {
            return $cleaned;
        }

        // International dialling prefix 00XX → +XX
        if (str_starts_with($cleaned, '00')) {
            return '+' . substr($cleaned, 2);
        }

        // Local number starting with 0 — strip the trunk 0 and prepend country code
        if (str_starts_with($cleaned, '0')) {
            $cc = ltrim($this->defaultCountryCode, '+');
            return '+' . $cc . substr($cleaned, 1);
        }

        // Bare number without any prefix — prepend country code as-is
        return $this->defaultCountryCode . $cleaned;
    }
}
