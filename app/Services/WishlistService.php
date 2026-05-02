<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class WishlistService
{
    public function list(User $user): LengthAwarePaginator
    {
        return $user->wishlistProducts()
            ->with(['images', 'store', 'category', 'condition'])
            ->latest('wishlists.created_at')
            ->paginate(15);
    }

    public function add(User $user, Product $product): void
    {
        if ($product->status !== 'published') {
            throw ValidationException::withMessages([
                'product_id' => ['Only published products can be added to wishlist.'],
            ]);
        }

        $user->wishlistProducts()->syncWithoutDetaching([$product->id]);
    }

    public function remove(User $user, Product $product): void
    {
        $user->wishlistProducts()->detach($product->id);
    }
}
