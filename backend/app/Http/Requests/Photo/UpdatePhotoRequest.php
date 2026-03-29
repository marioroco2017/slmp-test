<?php

namespace App\Http\Requests\Photo;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'album_id'      => ['sometimes', 'required', 'integer', 'exists:albums,id'],
            'title'         => ['sometimes', 'required', 'string', 'max:255'],
            'url'           => ['sometimes', 'required', 'url', 'max:500'],
            'thumbnail_url' => ['sometimes', 'required', 'url', 'max:500'],
        ];
    }
}
