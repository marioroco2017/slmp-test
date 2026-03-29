<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Album\StoreAlbumRequest;
use App\Http\Requests\Album\UpdateAlbumRequest;
use App\Http\Resources\AlbumResource;
use App\Http\Resources\PhotoResource;
use App\Models\Album;
use App\Services\AlbumService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class AlbumController extends Controller
{
    public function __construct(private readonly AlbumService $albumService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {

        $this->authorize('viewAny', Album::class);

        $posts = $this->albumService->paginate($request->user());

        return AlbumResource::collection($posts);


    }


    public function show(Album $album): AlbumResource
    {
        $this->authorize('view', $album);

        return new AlbumResource($album);
    }

    public function store(StoreAlbumRequest $request): JsonResponse
    {
        $this->authorize('create', Album::class);

        try {
            $album = $this->albumService->create($request->user(), $request->validated());

            return AlbumResource::make($album)->response()->setStatusCode(201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to create album.',
                'error' => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function update(UpdateAlbumRequest $request, Album $album): JsonResponse
    {
        $this->authorize('update', $album);

        try {
            $updated = $this->albumService->update($album, $request->validated());

            return AlbumResource::make($updated)->response();
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to update album.',
                'error' => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy(Album $album): JsonResponse
    {
        $this->authorize('delete', $album);

        try {
            $this->albumService->delete($album);

            return response()->json(null, 204);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to delete album.',
                'error' => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function photos(Album $album): AnonymousResourceCollection
    {
        $this->authorize('view', $album);

        return PhotoResource::collection($this->albumService->paginatePhotos($album));
    }
}
