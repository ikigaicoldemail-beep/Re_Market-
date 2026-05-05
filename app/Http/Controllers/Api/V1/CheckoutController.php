<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Address;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $checkoutService) {}

    public function store(CheckoutRequest $request): JsonResponse
    {
        $address = Address::findOrFail($request->validated('address_id'));
        $order = $this->checkoutService->checkout($request->user(), $address, $request->validated());

        return response()->json([
            'message' => 'Checkout completed successfully.',
            'order' => new OrderResource($order),
        ], 201);
    }
}
