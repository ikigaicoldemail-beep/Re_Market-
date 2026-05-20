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

    public function publicIndex(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'sort' => ['nullable', 'in:latest,followers,name'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:48'],
        ]);

        $stores = Store::query()
            ->where('status', 'active')
            ->withCount(['products' => fn ($q) => $q->where('status', 'published')->where('visibility', 'public')])
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('city', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['city'] ?? null, fn ($q, $city) => $q->where('city', $city))
            ->when(isset($filters['country_code']), fn ($q) => $q->where('country_code', strtoupper((string) $filters['country_code'])))
            ->when(($filters['sort'] ?? 'latest') === 'followers', fn ($q) => $q->orderByDesc('followers_count'))
            ->when(($filters['sort'] ?? 'latest') === 'name', fn ($q) => $q->orderBy('name'))
            ->when(($filters['sort'] ?? 'latest') === 'latest', fn ($q) => $q->latest('published_at'))
            ->paginate($filters['per_page'] ?? 24)
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

    public function myStore(Request $request): JsonResponse
    {
        $store = $request->user()->stores()->latest('id')->first();

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
