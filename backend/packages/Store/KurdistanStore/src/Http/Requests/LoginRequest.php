<?php

namespace Store\KurdistanStore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Store\KurdistanStore\Services\Phone\PhoneNormalizer;

class LoginRequest extends FormRequest
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
            'phone'    => ['required', 'string', 'max:20'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }
}
