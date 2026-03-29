<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhotoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'album_id'      => $this->album_id,
            'title'         => $this->title,
            'url'           => $this->url,
            'thumbnail_url' => $this->thumbnail_url,
        ];
    }
}
