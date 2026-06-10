<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Notifications\OrderCancelled;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class OrderController extends Controller
{
    public function __construct(private readonly CheckoutService $checkoutService) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $this->checkoutService->orderHistory($request->user());

        return response()->json([
            'orders' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['address', 'store', 'items', 'payments']);

        return response()->json([
            'order' => new OrderResource($order),
        ]);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        if ($order->buyer_id !== $request->user()->id) {
            abort(403, 'Only the buyer can cancel this order.');
        }

        if (! in_array($order->status, ['pending', 'processing'], true)) {
            abort(422, 'Order is "'.$order->status.'" and can no longer be cancelled.');
        }

        if ($order->payment_status === 'paid') {
            abort(422, 'Paid orders must be refunded by the seller, not cancelled by the buyer.');
        }

        $order->fill([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ])->save();

        $order->load(['buyer.profile', 'address', 'store', 'items.seller', 'payments']);

        // Notify the seller(s) — unique by seller_id (since orders can have items from one seller, but be safe).
        try {
            $sellers = $order->items->pluck('seller')->filter()->unique('id');
            if ($sellers->isNotEmpty()) {
                Notification::send($sellers, new OrderCancelled($order));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Order cancelled.',
            'order' => new OrderResource($order),
        ]);
    }
}
