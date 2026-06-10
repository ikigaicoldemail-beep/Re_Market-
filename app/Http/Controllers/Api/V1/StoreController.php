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
            'sort' => ['nullable', 'in:latest,followers,name,nearest'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:48'],
            // Geolocation for "stores near me"
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
            'radius_km' => ['nullable', 'numeric', 'min:0.1', 'max:20000'],
        ]);

        $lat = $filters['lat'] ?? null;
        $lng = $filters['lng'] ?? null;
        $hasGeo = $lat !== null && $lng !== null;
        $radius = $filters['radius_km'] ?? null;

        // "nearest" only makes sense with coordinates; otherwise fall back to latest.
        $sort = $filters['sort'] ?? 'latest';
        if ($sort === 'nearest' && ! $hasGeo) {
            $sort = 'latest';
        }

        // Numerically-stable haversine (asin form): great-circle distance in km.
        // NaN-free at distance 0, and uses only sin/cos/asin/sqrt/radians so it
        // runs on both MySQL (production) and SQLite (tests).
        $distanceSql =
            '(2 * 6371 * asin(sqrt('
            .'((sin((radians(stores.latitude) - radians(?)) / 2)) * (sin((radians(stores.latitude) - radians(?)) / 2)))'
            .' + cos(radians(?)) * cos(radians(stores.latitude))'
            .' * ((sin((radians(stores.longitude) - radians(?)) / 2)) * (sin((radians(stores.longitude) - radians(?)) / 2)))'
            .')))';
        $distanceBindings = [$lat, $lat, $lat, $lng, $lng];

        $stores = Store::query()
            ->where('status', 'active')
            ->withCount(['products' => fn ($q) => $q->where('status', 'published')->where('visibility', 'public')])
            ->when($hasGeo, fn ($q) => $q
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->selectRaw("$distanceSql as distance_km", $distanceBindings))
            ->when($hasGeo && $radius !== null, fn ($q) => $q
                ->whereRaw("$distanceSql <= ?", [...$distanceBindings, $radius]))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('city', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['city'] ?? null, fn ($q, $city) => $q->where('city', $city))
            ->when(isset($filters['country_code']), fn ($q) => $q->where('country_code', strtoupper((string) $filters['country_code'])))
            ->when($sort === 'nearest', fn ($q) => $q->orderBy('distance_km'))
            ->when($sort === 'followers', fn ($q) => $q->orderByDesc('followers_count'))
            ->when($sort === 'name', fn ($q) => $q->orderBy('name'))
            ->when($sort === 'latest', fn ($q) => $q->latest('published_at'))
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
        $store->load('user');

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
