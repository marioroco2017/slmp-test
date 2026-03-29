<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): array
    {
        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'username' => $data['username'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'phone'    => $data['phone'] ?? null,
                'website'  => $data['website'] ?? null,
            ]);

            if (! empty($data['address'])) {
                $address = $user->address()->create([
                    'street'  => $data['address']['street'] ?? null,
                    'suite'   => $data['address']['suite'] ?? null,
                    'city'    => $data['address']['city'] ?? null,
                    'zipcode' => $data['address']['zipcode'] ?? null,
                ]);

                if (! empty($data['address']['geo'])) {
                    $address->geo()->create([
                        'lat' => $data['address']['geo']['lat'] ?? null,
                        'lng' => $data['address']['geo']['lng'] ?? null,
                    ]);
                }
            }

            if (! empty($data['company'])) {
                $user->company()->create([
                    'name'         => $data['company']['name'] ?? null,
                    'catch_phrase' => $data['company']['catchPhrase'] ?? null,
                    'bs'           => $data['company']['bs'] ?? null,
                ]);
            }

            return $user;
        });

        $token = $user->createToken('auth_token')->plainTextToken;

        return ['user' => $user->load(['address.geo', 'company']), 'token' => $token];
    }

    public function login(): array
    {
        /** @var User $user */
        $user  = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return ['user' => $user->load(['address.geo', 'company']), 'token' => $token];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
