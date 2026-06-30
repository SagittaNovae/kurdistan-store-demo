<?php

namespace Store\KurdistanStore\Services\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Store\KurdistanStore\Models\CustomerRefreshToken;
use Symfony\Component\HttpFoundation\Cookie;
use Webkul\Customer\Models\Customer;

class TokenService
{
    private const ACCESS_TOKEN_MINUTES   = 60;
    private const REFRESH_DAYS           = 7;
    private const REFRESH_DAYS_REMEMBER  = 30;
    private const COOKIE_NAME            = 'refresh_token';

    /**
     * Issue a Sanctum access token + refresh token pair for the given customer.
     *
     * @return array{access_token: string, refresh_token: string, expires_in: int}
     */
    public function issueTokenPair(Customer $customer, bool $remember = false, ?Request $request = null): array
    {
        $req = $request ?? request();

        $accessModel = $customer->createToken(
            'web',
            ['*'],
            now()->addMinutes(self::ACCESS_TOKEN_MINUTES)
        );

        $plain = Str::random(64);

        CustomerRefreshToken::create([
            'customer_id' => $customer->id,
            'token_hash'  => hash('sha256', $plain),
            'expires_at'  => now()->addDays($remember ? self::REFRESH_DAYS_REMEMBER : self::REFRESH_DAYS),
            'remember'    => $remember,
            'user_agent'  => $req->userAgent(),
            'ip_address'  => $req->ip(),
        ]);

        return [
            'access_token'  => $accessModel->plainTextToken,
            'refresh_token' => $plain,
            'expires_in'    => self::ACCESS_TOKEN_MINUTES * 60,
        ];
    }

    /**
     * Validate, rotate, and return a new token pair.
     * Returns null if the refresh token is missing, revoked, or expired.
     *
     * @return array{access_token: string, refresh_token: string, expires_in: int, remember: bool}|null
     */
    public function refreshTokenPair(string $plain): ?array
    {
        $hash   = hash('sha256', $plain);
        $record = CustomerRefreshToken::where('token_hash', $hash)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->with('customer')
            ->first();

        if (! $record || ! $record->customer) {
            return null;
        }

        $remember = $record->remember;

        // Token rotation — revoke old record, issue new pair
        $record->update(['revoked_at' => now()]);

        $tokens             = $this->issueTokenPair($record->customer, $remember);
        $tokens['remember'] = $remember;

        return $tokens;
    }

    /**
     * Revoke a single refresh token by its plain value.
     */
    public function revokeByPlainToken(string $plain): void
    {
        CustomerRefreshToken::where('token_hash', hash('sha256', $plain))
            ->update(['revoked_at' => now()]);
    }

    /**
     * Revoke all refresh tokens for a customer and delete their Sanctum access tokens.
     * Used for logout-all-devices.
     */
    public function revokeAllForCustomer(int $customerId): void
    {
        CustomerRefreshToken::where('customer_id', $customerId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        Customer::find($customerId)?->tokens()->delete();
    }

    /**
     * Build a Set-Cookie directive for the refresh token (HttpOnly, Secure in prod).
     */
    public function makeRefreshCookie(string $plain, bool $remember = false): Cookie
    {
        $isProd  = config('app.env') === 'production';
        $minutes = ($remember ? self::REFRESH_DAYS_REMEMBER : self::REFRESH_DAYS) * 24 * 60;

        return cookie(
            self::COOKIE_NAME,
            $plain,
            $minutes,
            '/',
            null,
            $isProd,
            true,   // httpOnly — not readable by JS
            false,
            $isProd ? 'strict' : 'lax',
        );
    }

    /**
     * Build an expired cookie to clear the refresh token from the browser.
     */
    public function clearRefreshCookie(): Cookie
    {
        $isProd = config('app.env') === 'production';

        return cookie(self::COOKIE_NAME, '', -1, '/', null, $isProd, true, false, $isProd ? 'strict' : 'lax');
    }
}
