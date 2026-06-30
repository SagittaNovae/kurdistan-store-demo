<?php

namespace Store\KurdistanStore\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'label'        => $this->label,
            'nickname'     => $this->nickname,
            'address_text' => $this->address_text,
            'governorate'  => $this->governorate,
            'city'         => $this->city,
            'address_line' => $this->address_line,
            'latitude'     => (float) $this->latitude,
            'longitude'    => (float) $this->longitude,
            'is_default'   => (bool) $this->is_default,
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }
}
