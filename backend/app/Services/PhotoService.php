<?php

namespace App\Services;

use App\Models\Photo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PhotoService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Photo::paginate($perPage);
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
