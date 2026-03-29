<?php

namespace App\Services;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TodoService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Todo::paginate($perPage);
    }

    public function create(User $user, array $data): Todo
    {
        return $user->todos()->create($data);
    }

    public function update(Todo $todo, array $data): Todo
    {
        $todo->update($data);

        return $todo;
    }

    public function delete(Todo $todo): void
    {
        $todo->delete();
    }
}
