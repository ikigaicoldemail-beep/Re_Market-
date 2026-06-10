<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\ImageSimilaritySearchRequest;
use App\Http\Resources\AiSearchLogResource;
use App\Http\Resources\ProductResource;
use App\Services\AiSimilaritySearchService;
use Illuminate\Http\JsonResponse;

class AiSimilaritySearchController extends Controller
{
    public function __construct(private readonly AiSimilaritySearchService $similaritySearchService) {}

    public function store(ImageSimilaritySearchRequest $request): JsonResponse
    {
        [$log, $products] = $this->similaritySearchService->search($request->user(), $request->validated());

        return response()->json([
            'message' => 'Similarity search completed.',
            'log' => new AiSearchLogResource($log),
            'products' => ProductResource::collection($products),
        ]);
    }
}
