<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(private readonly CheckoutService $checkoutService)
    {
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $payment = $this->checkoutService->paymentForOrder($order);

        return response()->json([
            'payment' => $payment ? new PaymentResource($payment) : null,
        ]);
    }
}
