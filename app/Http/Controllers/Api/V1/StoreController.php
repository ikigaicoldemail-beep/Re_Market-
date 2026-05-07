<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StoreUpsertRequest;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private readonly StoreService $storeService) {}

    public function myStore(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (! $store) {
            return response()->json([
                'message' => 'You do not have a store yet.',
                'store' => null,
            ], 404);
        }

        return response()->json([
            'store' => new StoreResource($store),
        ]);
    }

    public function show(Store $store): JsonResponse
    {
        return response()->json([
            'store' => new StoreResource($store),
        ]);
    }

    public function store(StoreUpsertRequest $request): JsonResponse
    {
        $store = $this->storeService->create($request->user(), $request->validated());

        return response()->json([
            'message' => 'Store created successfully.',
            'store' => new StoreResource($store),
        ], 201);
    }

    public function update(StoreUpsertRequest $request, Store $store): JsonResponse
    {
        $this->authorize('update', $store);

        $store = $this->storeService->update($store, $request->validated());

        return response()->json([
            'message' => 'Store updated successfully.',
            'store' => new StoreResource($store),
        ]);
    }
}
