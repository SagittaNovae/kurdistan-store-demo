<?php

namespace Store\KurdistanStore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Store\KurdistanStore\Services\Phone\PhoneNormalizer;

class CheckoutRequest extends FormRequest
{
    // Iraq geographic bounding box (WGS-84)
    private const IRAQ_LAT_MIN = 29.06;
    private const IRAQ_LAT_MAX = 37.38;
    private const IRAQ_LNG_MIN = 38.79;
    private const IRAQ_LNG_MAX = 48.76;

    public function authorize(): bool
    {
        // Guest checkout is allowed — no authentication or phone verification required.
        // Authenticated users with unverified phones may also proceed (guest-parity scope).
        return true;
    }

    /** Normalise the phone to E.164 before validation runs, matching RegisterRequest / LoginRequest. */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone') && $this->input('phone') !== null) {
            $this->merge([
                'phone' => app(PhoneNormalizer::class)->normalize((string) $this->input('phone')),
            ]);
        }
    }

    public function rules(): array
    {
        $locations    = config('kurdistan-store.locations', []);
        $governorates = array_keys($locations);

        return [
            'first_name'     => ['required', 'string', 'max:100'],
            'last_name'      => ['required', 'string', 'max:100'],
            'phone'          => ['required', 'string', 'max:20'],
            'governorate'    => ['required', 'string', Rule::in($governorates)],
            'city'           => [
                'required',
                'string',
                'max:100',
                function (string $attribute, mixed $value, \Closure $fail) use ($locations) {
                    $governorate = $this->input('governorate');

                    if ($governorate && isset($locations[$governorate])) {
                        if (! in_array($value, $locations[$governorate], true)) {
                            $fail('The selected city is not valid for the chosen governorate.');
                        }
                    }
                },
            ],
            'address'            => ['required', 'string', 'max:500'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'payment_method'     => ['required', 'string', Rule::in(['cashondelivery'])],
            'delivery_latitude'       => ['required', 'numeric', 'between:-90,90'],
            'delivery_longitude'      => ['required', 'numeric', 'between:-180,180'],
            'delivery_address_text'   => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($v) {
            if ($v->errors()->hasAny(['delivery_latitude', 'delivery_longitude'])) {
                return;
            }

            $lat = (float) $this->input('delivery_latitude');
            $lng = (float) $this->input('delivery_longitude');

            $insideIraq = $lat >= self::IRAQ_LAT_MIN && $lat <= self::IRAQ_LAT_MAX
                && $lng >= self::IRAQ_LNG_MIN && $lng <= self::IRAQ_LNG_MAX;

            if (! $insideIraq) {
                $v->errors()->add(
                    'delivery_latitude',
                    'Delivery is only available within Iraq. Please select a location inside Iraq.'
                );
            }
        });
    }
}
