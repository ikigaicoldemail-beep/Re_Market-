<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\VisualSearchRequest;
use App\Http\Resources\ProductResource;
use App\Services\VisualSearchService;
use Illuminate\Http\JsonResponse;

class VisualSearchController extends Controller
{
    public function __construct(private readonly VisualSearchService $visualSearch) {}

    public function __invoke(VisualSearchRequest $request): JsonResponse
    {
        $products = $this->visualSearch->search(
            $request->file('image'),
            (int) ($request->validated('limit') ?? 24)
        );

        return response()->json([
            'products' => ProductResource::collection($products),
            'meta' => [
                'total' => $products->count(),
                'mode' => 'visual',
            ],
        ]);
    }
}
