<?php

namespace Store\KurdistanStore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Store\KurdistanStore\Services\Phone\PhoneNormalizer;
use Store\KurdistanStore\Services\Phone\PhoneValidator;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Normalise the phone to E.164 before validation runs. */
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
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'phone'      => [
                'required',
                'string',
                'unique:customers,phone',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! app(PhoneValidator::class)->isValid($value)) {
                        $fail(app(PhoneValidator::class)->message());
                    }
                },
            ],
            'email'    => ['nullable', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }
}
