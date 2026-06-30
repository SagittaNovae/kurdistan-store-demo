<?php

namespace Store\KurdistanStore\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Store\KurdistanStore\Http\Requests\LoginRequest;
use Store\KurdistanStore\Http\Requests\RegisterRequest;
use Store\KurdistanStore\Http\Resources\CustomerResource;
use Store\KurdistanStore\Services\Auth\TokenService;
use Webkul\Customer\Repositories\CustomerRepository;

class AuthController extends Controller
{
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected TokenService $tokenService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $customer = $this->customerRepository->create([
            'first_name'        => $data['first_name'],
            'last_name'         => $data['last_name'],
            'phone'             => $data['phone'],
            'email'             => $data['email'] ?? null,
            'password'          => bcrypt($data['password']),
            'customer_group_id' => 2,
            'channel_id'        => core()->getCurrentChannel()->id,
            'is_verified'       => true,
            'is_phone_verified' => true,
            'status'            => 1,
        ]);

        Auth::guard('customer')->login($customer);

        $tokens = $this->tokenService->issueTokenPair($customer, false, $request);

        return response()
            ->json([
                'data'         => new CustomerResource($customer),
                'access_token' => $tokens['access_token'],
                'expires_in'   => $tokens['expires_in'],
                'message'      => 'Registration successful.',
            ], 201)
            ->cookie($this->tokenService->makeRefreshCookie($tokens['refresh_token']));
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only(['phone', 'password']);
        $remember    = $request->boolean('remember');

        if (! Auth::guard('customer')->attempt($credentials, $remember)) {
            return response()->json(['message' => 'Invalid phone number or password.'], 401);
        }

        $customer = Auth::guard('customer')->user();
        $request->session()->regenerate();
        $tokens = $this->tokenService->issueTokenPair($customer, $remember, $request);

        return response()
            ->json([
                'data'         => new CustomerResource($customer),
                'access_token' => $tokens['access_token'],
                'expires_in'   => $tokens['expires_in'],
            ])
            ->cookie($this->tokenService->makeRefreshCookie($tokens['refresh_token'], $remember));
    }

    /**
     * Exchange a valid refresh token for a new access + refresh pair (token rotation).
     *
     * POST /auth/refresh
     *
     * For web SPA: the refresh token is read from the HttpOnly `refresh_token` cookie —
     *   the browser sends it automatically and it is never accessible to JavaScript.
     * For mobile clients: send `{"refresh_token": "..."}` in the request body instead.
     */
    public function refresh(Request $request): JsonResponse
    {
        $plain = $request->cookie('refresh_token') ?? $request->input('refresh_token');

        if (! $plain) {
            return response()->json(['message' => 'No refresh token provided.'], 401);
        }

        $tokens = $this->tokenService->refreshTokenPair($plain);

        if (! $tokens) {
            return response()
                ->json(['message' => 'Refresh token invalid or expired. Please log in again.'], 401)
                ->cookie($this->tokenService->clearRefreshCookie());
        }

        return response()
            ->json([
                'access_token' => $tokens['access_token'],
                'expires_in'   => $tokens['expires_in'],
            ])
            ->cookie($this->tokenService->makeRefreshCookie($tokens['refresh_token'], $tokens['remember']));
    }

    public function logout(Request $request): JsonResponse
    {
        $plain = $request->cookie('refresh_token') ?? $request->input('refresh_token');

        if ($plain) {
            $this->tokenService->revokeByPlainToken($plain);
        }

        // Also revoke the current Sanctum access token if authenticated via Bearer
        if ($request->user()) {
            $request->user()->currentAccessToken()?->delete();
        }

        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()
            ->json(['message' => 'Logged out.'])
            ->cookie($this->tokenService->clearRefreshCookie());
    }

    public function me(): JsonResponse
    {
        $customer = auth()->user();

        if (! $customer) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json(['data' => new CustomerResource($customer)]);
    }
}
