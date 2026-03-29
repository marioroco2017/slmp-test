<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return User::with(['address.geo', 'company'])->paginate($perPage);
    }

    public function findWithRelations(User $user): User
    {
        return $user->load(['address.geo', 'company']);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function paginatePosts(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->posts()->paginate($perPage);
    }

    public function paginateAlbums(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->albums()->paginate($perPage);
    }

    public function paginateTodos(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->todos()->paginate($perPage);
    }
}
