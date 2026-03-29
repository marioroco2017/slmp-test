<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'username'            => ['required', 'string', 'max:255', 'unique:users,username'],
            'email'               => ['required', 'email', 'unique:users,email'],
            'password'            => ['required', 'string', 'min:8', 'confirmed'],
            'phone'               => ['nullable', 'string', 'max:255'],
            'website'             => ['nullable', 'string', 'max:255'],

            'address'             => ['nullable', 'array'],
            'address.street'      => ['nullable', 'string', 'max:255'],
            'address.suite'       => ['nullable', 'string', 'max:255'],
            'address.city'        => ['nullable', 'string', 'max:255'],
            'address.zipcode'     => ['nullable', 'string', 'max:20'],
            'address.geo'         => ['nullable', 'array'],
            'address.geo.lat'     => ['nullable', 'numeric', 'between:-90,90'],
            'address.geo.lng'     => ['nullable', 'numeric', 'between:-180,180'],

            'company'             => ['nullable', 'array'],
            'company.name'        => ['nullable', 'string', 'max:255'],
            'company.catchPhrase' => ['nullable', 'string', 'max:255'],
            'company.bs'          => ['nullable', 'string', 'max:255'],
        ];
    }
}
