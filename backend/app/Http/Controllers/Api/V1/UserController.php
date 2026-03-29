<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\AlbumResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\TodoResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        return UserResource::collection($this->userService->paginate($request->user()));
    }

    public function show(User $user): UserResource
    {
        $this->authorize('view', $user);

        return new UserResource($this->userService->findWithRelations($user));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        try {
            $user = $this->userService->create($request->validated());

            return UserResource::make($user)->response()->setStatusCode(201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to create user.',
                'error'   => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        try {
            $updated = $this->userService->update($user, $request->validated());

            return UserResource::make($updated)->response();
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to update user.',
                'error'   => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        try {
            $this->userService->delete($user);

            return response()->json(null, 204);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to delete user.',
                'error'   => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function posts(User $user): AnonymousResourceCollection
    {
        $this->authorize('view', $user);

        return PostResource::collection($this->userService->paginatePosts($user));
    }

    public function albums(User $user): AnonymousResourceCollection
    {
        $this->authorize('view', $user);

        return AlbumResource::collection($this->userService->paginateAlbums($user));
    }

    public function todos(User $user): AnonymousResourceCollection
    {
        $this->authorize('view', $user);

        return TodoResource::collection($this->userService->paginateTodos($user));
    }
}
