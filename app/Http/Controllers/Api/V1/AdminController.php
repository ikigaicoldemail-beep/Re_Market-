<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StoreResource;
use App\Http\Resources\UserResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function users(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'role' => ['nullable', Rule::in(['user', 'seller', 'admin'])],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'suspended', 'banned'])],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $users = User::query()
            ->with(['profile.defaultStore', 'stores'])
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return response()->json([
            'users' => UserResource::collection($users),
            'meta' => $this->paginationMeta($users),
        ]);
    }

    public function updateUser(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user)],
            'role' => ['sometimes', Rule::in(['user', 'seller', 'admin'])],
            'status' => ['sometimes', Rule::in(['active', 'inactive', 'suspended', 'banned'])],
            'email_verified_at' => ['nullable', 'date'],
            'phone_verified_at' => ['nullable', 'date'],
        ]);

        if ($request->user()->is($user) && ($data['role'] ?? $user->role) !== 'admin') {
            abort(422, 'You cannot remove your own admin role.');
        }

        if ($request->user()->is($user) && isset($data['status']) && $data['status'] !== 'active') {
            abort(422, 'You cannot deactivate your own admin account.');
        }

        $user->fill($data)->save();

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => new UserResource($user->fresh(['profile.defaultStore', 'stores'])),
        ]);
    }

    public function deleteUser(Request $request, User $user): JsonResponse
    {
        if ($request->user()->is($user)) {
            abort(422, 'You cannot delete your own admin account.');
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    public function stores(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'seller_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', Rule::in(['draft', 'active', 'suspended', 'archived'])],
            'is_verified' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

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
            'meta' => $this->paginationMeta($stores),
        ]);
    }

    public function updateStore(Request $request, Store $store): JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(['draft', 'active', 'suspended', 'archived'])],
            'is_verified' => ['sometimes', 'boolean'],
        ]);

        $store->fill($data);

        if (($data['status'] ?? null) === 'active' && ! $store->published_at) {
            $store->published_at = now();
        }

        $store->save();

        return response()->json([
            'message' => 'Store updated successfully.',
            'store' => new StoreResource($store->fresh(['user.profile'])),
        ]);
    }

    public function deleteStore(Store $store): JsonResponse
    {
        $store->delete();

        return response()->json([
            'message' => 'Store deleted successfully.',
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'seller_id' => ['nullable', 'integer', 'exists:users,id'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'status' => ['nullable', Rule::in(['draft', 'pending', 'published', 'sold', 'inactive', 'archived'])],
            'moderation_status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $products = Product::query()
            ->with(['images', 'store', 'category', 'condition', 'user.profile'])
            ->when($filters['seller_id'] ?? null, fn ($query, $sellerId) => $query->where('user_id', $sellerId))
            ->when($filters['store_id'] ?? null, fn ($query, $storeId) => $query->where('store_id', $storeId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['moderation_status'] ?? null, fn ($query, $status) => $query->where('moderation_status', $status))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('title', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return response()->json([
            'products' => ProductResource::collection($products),
            'meta' => $this->paginationMeta($products),
        ]);
    }

    public function updateProduct(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(['draft', 'pending', 'published', 'sold', 'inactive', 'archived'])],
            'moderation_status' => ['sometimes', Rule::in(['pending', 'approved', 'rejected'])],
            'visibility' => ['sometimes', Rule::in(['public', 'followers_only', 'private'])],
            'stock_quantity' => ['sometimes', 'integer', 'min:0'],
        ]);

        $product->fill($data);

        if (($data['status'] ?? null) === 'published' && ! $product->published_at) {
            $product->published_at = now();
        }

        $product->save();

        return response()->json([
            'message' => 'Product updated successfully.',
            'product' => new ProductResource($product->fresh(['images', 'store', 'category', 'condition', 'user.profile'])),
        ]);
    }

    public function deleteProduct(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'buyer_id' => ['nullable', 'integer', 'exists:users,id'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'status' => ['nullable', Rule::in(['pending', 'processing', 'completed', 'cancelled', 'refunded'])],
            'payment_status' => ['nullable', Rule::in(['pending', 'paid', 'failed', 'refunded'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $orders = Order::query()
            ->with(['buyer.profile', 'address', 'store', 'items', 'payments'])
            ->when($filters['buyer_id'] ?? null, fn ($query, $buyerId) => $query->where('buyer_id', $buyerId))
            ->when($filters['store_id'] ?? null, fn ($query, $storeId) => $query->where('store_id', $storeId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['payment_status'] ?? null, fn ($query, $status) => $query->where('payment_status', $status))
            ->latest()
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return response()->json([
            'orders' => OrderResource::collection($orders),
            'meta' => $this->paginationMeta($orders),
        ]);
    }

    public function updateOrder(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(['pending', 'processing', 'completed', 'cancelled', 'refunded'])],
            'payment_status' => ['sometimes', Rule::in(['pending', 'paid', 'failed', 'refunded'])],
        ]);

        $order->fill($data);

        if (($data['payment_status'] ?? null) === 'paid' && ! $order->paid_at) {
            $order->paid_at = now();
        }

        if (($data['status'] ?? null) === 'cancelled' && ! $order->cancelled_at) {
            $order->cancelled_at = now();
        }

        if (($data['status'] ?? null) === 'completed' && ! $order->completed_at) {
            $order->completed_at = now();
        }

        $order->save();

        return response()->json([
            'message' => 'Order updated successfully.',
            'order' => new OrderResource($order->fresh(['buyer.profile', 'address', 'store', 'items', 'payments'])),
        ]);
    }

    private function paginationMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
