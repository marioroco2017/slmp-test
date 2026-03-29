<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Photo\StorePhotoRequest;
use App\Http\Requests\Photo\UpdatePhotoRequest;
use App\Http\Resources\PhotoResource;
use App\Models\Photo;
use App\Services\PhotoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class PhotoController extends Controller
{
    public function __construct(private readonly PhotoService $photoService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Photo::class);

        return PhotoResource::collection($this->photoService->paginate($request->user()));
    }

    public function show(Photo $photo): PhotoResource
    {
        $this->authorize('view', $photo);

        return new PhotoResource($photo);
    }

    public function store(StorePhotoRequest $request): JsonResponse
    {
        $this->authorize('create', Photo::class);

        try {
            $photo = $this->photoService->create($request->validated());

            return PhotoResource::make($photo)->response()->setStatusCode(201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to create photo.',
                'error'   => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function update(UpdatePhotoRequest $request, Photo $photo): JsonResponse
    {
        $this->authorize('update', $photo);

        try {
            $updated = $this->photoService->update($photo, $request->validated());

            return PhotoResource::make($updated)->response();
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to update photo.',
                'error'   => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy(Photo $photo): JsonResponse
    {
        $this->authorize('delete', $photo);

        try {
            $this->photoService->delete($photo);

            return response()->json(null, 204);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to delete photo.',
                'error'   => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }
}
