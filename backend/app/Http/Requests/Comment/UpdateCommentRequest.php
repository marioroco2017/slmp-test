<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'post_id' => ['sometimes', 'required', 'integer', 'exists:posts,id'],
            'name'    => ['sometimes', 'required', 'string', 'max:255'],
            'email'   => ['sometimes', 'required', 'email', 'max:255'],
            'body'    => ['sometimes', 'required', 'string'],
        ];
    }
}
