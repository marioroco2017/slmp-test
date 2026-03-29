<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'street'  => $this->street,
            'suite'   => $this->suite,
            'city'    => $this->city,
            'zipcode' => $this->zipcode,
            'geo'     => new GeoResource($this->whenLoaded('geo')),
        ];
    }
}
