<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductReportResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StoreResource;
use App\Http\Resources\UserResource;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function stats(): JsonResponse
    {
        $stats = Cache::remember('admin:stats:v1', 60, function () {
            return [
                'users' => [
                    'total' => User::count(),
                    'active' => User::where('status', 'active')->count(),
                    'sellers' => User::where('role', 'seller')->count(),
                ],
                'stores' => [
                    'total' => Store::count(),
                    'active' => Store::where('status', 'active')->count(),
                    'pending_verification' => Store::where('is_verified', false)->where('status', 'active')->count(),
                ],
                'products' => [
                    'total' => Product::count(),
                    'published' => Product::where('status', 'published')->count(),
                    'pending_moderation' => Product::where('moderation_status', 'pending')->count(),
                ],
                'orders' => [
                    'total' => Order::count(),
                    'paid' => Order::where('payment_status', 'paid')->count(),
                    'pending' => Order::where('status', 'pending')->count(),
                ],
                'revenue' => [
                    'paid_total_minor' => (int) Order::where('payment_status', 'paid')->sum('total_amount'),
                    'currency' => Order::where('payment_status', 'paid')->value('currency') ?? 'USD',
                ],
                'reports' => [
                    'open' => ProductReport::where('status', 'open')->count(),
                ],
            ];
        });

        return response()->json(['stats' => $stats]);
    }

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

    public function categories(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $categories = Category::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'categories' => CategoryResource::collection($categories),
        ]);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer'],
            'logo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ]);

        $payload = [
            'name' => $data['name'],
            'parent_id' => $data['parent_id'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
            'sort_order' => $data['sort_order'] ?? 0,
            'slug' => $this->uniqueCategorySlug($data['name']),
        ];

        if ($request->hasFile('logo')) {
            $payload['logo_path'] = $request->file('logo')->store('', 'category-logos');
            $payload['logo_disk'] = 'category-logos';
        }

        $category = Category::create($payload);

        return response()->json([
            'message' => 'Category created successfully.',
            'category' => new CategoryResource($category),
        ], 201);
    }

    public function updateCategory(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id', Rule::notIn([$category->id])],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'sort_order' => ['sometimes', 'integer'],
            'logo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'remove_logo' => ['sometimes', 'boolean'],
        ]);

        $category->fill(collect($data)->except(['logo', 'remove_logo'])->all());

        if ($request->hasFile('logo')) {
            if ($category->logo_path && $category->logo_disk) {
                Storage::disk($category->logo_disk)->delete($category->logo_path);
            }
            $category->logo_path = $request->file('logo')->store('', 'category-logos');
            $category->logo_disk = 'category-logos';
        } elseif ($request->boolean('remove_logo') && $category->logo_path && $category->logo_disk) {
            Storage::disk($category->logo_disk)->delete($category->logo_path);
            $category->logo_path = null;
            $category->logo_disk = null;
        }

        $category->save();

        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => new CategoryResource($category->fresh()),
        ]);
    }

    public function destroyCategory(Category $category): JsonResponse
    {
        if ($category->slug === 'other') {
            abort(422, 'The "Other" category cannot be deleted — it is used as the fallback for products without a category.');
        }

        $other = Category::firstOrCreate(
            ['slug' => 'other'],
            ['name' => 'Other', 'description' => 'Items that don\'t fit any other category.', 'status' => 'active', 'sort_order' => 99]
        );

        Product::where('category_id', $category->id)->update(['category_id' => $other->id]);
        Category::where('parent_id', $category->id)->update(['parent_id' => null]);

        if ($category->logo_path && $category->logo_disk) {
            Storage::disk($category->logo_disk)->delete($category->logo_path);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted. Affected products were moved to "Other".',
        ]);
    }

    public function reports(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(ProductReport::STATUSES)],
            'reason' => ['nullable', 'string', 'max:64'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $reports = ProductReport::query()
            ->with(['product.images', 'product.store', 'reporter.profile', 'resolver.profile'])
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['reason'] ?? null, fn ($q, $v) => $q->where('reason', $v))
            ->when($filters['product_id'] ?? null, fn ($q, $v) => $q->where('product_id', $v))
            ->orderByRaw("FIELD(status, 'open') DESC")
            ->latest()
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();

        return response()->json([
            'reports' => ProductReportResource::collection($reports),
            'meta' => $this->paginationMeta($reports),
        ]);
    }

    public function updateReport(Request $request, ProductReport $report): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['resolved', 'dismissed'])],
            'resolution_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $report->fill([
            'status' => $data['status'],
            'resolution_note' => $data['resolution_note'] ?? $report->resolution_note,
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ])->save();

        Cache::forget('admin:stats:v1');

        return response()->json([
            'message' => 'Report '.$data['status'].'.',
            'report' => new ProductReportResource($report->fresh(['product.images', 'product.store', 'reporter.profile', 'resolver.profile'])),
        ]);
    }

    private function uniqueCategorySlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
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
