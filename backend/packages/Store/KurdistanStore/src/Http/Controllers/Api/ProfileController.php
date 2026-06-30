<?php

namespace Store\KurdistanStore\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Store\KurdistanStore\Http\Resources\CustomerResource;
use Store\KurdistanStore\Models\CustomerPreference;
use Store\KurdistanStore\Services\Auth\TokenService;
use Webkul\Customer\Repositories\CustomerRepository;

class ProfileController extends Controller
{
    public function __construct(
        private readonly CustomerRepository $customers,
        private readonly TokenService $tokens,
    ) {}

    public function show(): JsonResponse
    {
        return response()->json(['data' => new CustomerResource(auth()->user())]);
    }

    public function update(Request $request): JsonResponse
    {
        $customer = auth()->user();

        $validated = $request->validate([
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name'  => ['sometimes', 'required', 'string', 'max:100'],
            'email'      => ['sometimes', 'nullable', 'email', 'max:255'],
        ]);

        foreach (['first_name', 'last_name'] as $field) {
            if (isset($validated[$field]) && trim($validated[$field]) === '') {
                return response()->json(['message' => 'Name fields cannot be empty.'], 422);
            }
        }

        $customer->fill($validated)->save();

        return response()->json(['data' => new CustomerResource($customer->fresh())]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $customer = auth()->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($validated['current_password'], $customer->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $customer->password = bcrypt($validated['new_password']);
        $customer->save();

        return response()->json(['message' => 'Password updated successfully.']);
    }

    public function getPreferences(): JsonResponse
    {
        $prefs = CustomerPreference::firstOrCreate(
            ['customer_id' => auth()->id()],
            [
                'notify_order_updates' => true,
                'notify_promotions'    => false,
                'preferred_language'   => 'en',
            ]
        );

        return response()->json(['data' => $this->preferencesArray($prefs)]);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'notify_order_updates' => ['sometimes', 'boolean'],
            'notify_promotions'    => ['sometimes', 'boolean'],
            'preferred_language'   => ['sometimes', 'string', 'in:en,ar,ku'],
        ]);

        $prefs = CustomerPreference::updateOrCreate(
            ['customer_id' => auth()->id()],
            $validated,
        );

        return response()->json(['data' => $this->preferencesArray($prefs->fresh())]);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $customer = auth()->user();
        $this->tokens->revokeAllForCustomer($customer->id);
        $customer->tokens()->delete();

        return response()->json(['message' => 'Signed out of all devices.']);
    }

    private function preferencesArray(CustomerPreference $prefs): array
    {
        return [
            'notify_order_updates' => (bool) $prefs->notify_order_updates,
            'notify_promotions'    => (bool) $prefs->notify_promotions,
            'preferred_language'   => $prefs->preferred_language,
        ];
    }
}
