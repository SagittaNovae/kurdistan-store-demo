<?php

namespace Store\KurdistanStore\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => trim(($this->first_name ?? '').' '.($this->last_name ?? '')),
            'first_name'         => $this->first_name,
            'last_name'          => $this->last_name,
            'email'              => $this->email,
            'phone'    => $this->phone,
            'is_admin' => false,
        ];
    }
}
