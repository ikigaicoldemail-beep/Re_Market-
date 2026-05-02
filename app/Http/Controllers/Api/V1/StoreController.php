<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListStoresRequest;
use App\Http\Requests\Store\StoreUpsertRequest;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    public function __construct(private readonly StoreService $storeService)
    {
    }

    public function adminIndex(ListStoresRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $stores = Store::query()
            ->with(['user.profile'])
            ->when($filters['seller_id'] ?? null, fn ($query, $sellerId) => $query->where('user_id', $sellerId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when(array_key_exists('is_verified', $filters), fn ($query) => $query->where('is_verified', $request->boolean('is_verified')))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('contact_email', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return response()->json([
            'stores' => StoreResource::collection($stores),
            'meta' => [
                'current_page' => $stores->currentPage(),
                'last_page' => $stores->lastPage(),
                'per_page' => $stores->perPage(),
                'total' => $stores->total(),
            ],
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
