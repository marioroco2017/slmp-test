<?php

namespace App\Http\Requests\Photo;

use Illuminate\Foundation\Http\FormRequest;

class StorePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'album_id'      => ['required', 'integer', 'exists:albums,id'],
            'title'         => ['required', 'string', 'max:255'],
            'url'           => ['required', 'url', 'max:500'],
            'thumbnail_url' => ['required', 'url', 'max:500'],
        ];
    }
}
