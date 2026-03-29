<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'post_id' => $this->post_id,
            'name'    => $this->name,
            'email'   => $this->email,
            'body'    => $this->body,
        ];
    }
}
