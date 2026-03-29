<?php

namespace App\Services;

use App\Models\Album;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AlbumService
{

    public function paginate(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->albums()->paginate($perPage);
    }

    public function create(User $user, array $data): Album
    {
        return $user->albums()->create($data);
    }

    public function update(Album $album, array $data): Album
    {
        $album->update($data);

        return $album;
    }

    public function delete(Album $album): void
    {
        $album->delete();
    }

    public function paginatePhotos(Album $album, int $perPage = 15): LengthAwarePaginator
    {
        return $album->photos()->paginate($perPage);
    }
}
