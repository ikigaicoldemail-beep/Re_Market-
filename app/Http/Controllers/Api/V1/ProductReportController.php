<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductReportResource;
use App\Models\Product;
use App\Models\ProductReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductReportController extends Controller
{
    public function store(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', Rule::in(array_keys(ProductReport::REASONS))],
            'details' => ['nullable', 'string', 'max:2000'],
        ]);

        $userId = $request->user()->id;

        if ($product->user_id === $userId) {
            abort(422, 'You cannot report your own product.');
        }

        $existing = ProductReport::where('product_id', $product->id)
            ->where('reporter_id', $userId)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            abort(409, 'You already have an open report for this product.');
        }

        $report = ProductReport::create([
            'product_id' => $product->id,
            'reporter_id' => $userId,
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
            'status' => 'open',
        ]);

        return response()->json([
            'message' => 'Report submitted. Thanks — our moderators will review it.',
            'report' => new ProductReportResource($report),
        ], 201);
    }

    public function reasons(): JsonResponse
    {
        return response()->json([
            'reasons' => collect(ProductReport::REASONS)
                ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                ->values(),
        ]);
    }
}
