<?php

namespace App\Services;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PhotoService
{
    public function paginate(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Photo::whereHas('album', fn ($q) => $q->where('user_id', $user->id))->paginate($perPage);
    }

    public function create(array $data): Photo
    {
        return Photo::create($data);
    }

    public function update(Photo $photo, array $data): Photo
    {
        $photo->update($data);

        return $photo;
    }

    public function delete(Photo $photo): void
    {
        $photo->delete();
    }
}
