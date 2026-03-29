<?php

namespace App\Policies;

use App\Models\Photo;
use App\Models\User;

class PhotoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Photo $photo): bool
    {
        return $user->id === $photo->album->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Photo $photo): bool
    {
        return $user->id === $photo->album->user_id;
    }

    public function delete(User $user, Photo $photo): bool
    {
        return $user->id === $photo->album->user_id;
    }
}
