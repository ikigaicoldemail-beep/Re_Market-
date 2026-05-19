<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Notifications\NewFollower;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreFollowController extends Controller
{
    public function follow(Request $request, Store $store): JsonResponse
    {
        $user = $request->user();

        if ($store->user_id === $user->id) {
            abort(422, 'You cannot follow your own store.');
        }

        $created = DB::transaction(function () use ($store, $user) {
            $existing = DB::table('store_followers')
                ->where('user_id', $user->id)
                ->where('store_id', $store->id)
                ->exists();

            if ($existing) {
                return false;
            }

            DB::table('store_followers')->insert([
                'user_id' => $user->id,
                'store_id' => $store->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $store->increment('followers_count');

            return true;
        });

        if ($created) {
            try {
                $owner = $store->user;
                if ($owner) {
                    $owner->notify(new NewFollower($store, $user));
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'message' => $created ? 'Now following.' : 'Already following.',
            'store' => new StoreResource($store->fresh()),
            'is_following' => true,
        ]);
    }

    public function unfollow(Request $request, Store $store): JsonResponse
    {
        $user = $request->user();

        $deleted = DB::transaction(function () use ($store, $user) {
            $rows = DB::table('store_followers')
                ->where('user_id', $user->id)
                ->where('store_id', $store->id)
                ->delete();
            if ($rows > 0 && $store->followers_count > 0) {
                $store->decrement('followers_count');
            }
            return $rows;
        });

        return response()->json([
            'message' => $deleted ? 'Unfollowed.' : 'You were not following this store.',
            'store' => new StoreResource($store->fresh()),
            'is_following' => false,
        ]);
    }

    public function followingStores(Request $request): JsonResponse
    {
        $stores = $request->user()
            ->belongsToMany(Store::class, 'store_followers')
            ->withTimestamps()
            ->with('user.profile')
            ->latest('store_followers.created_at')
            ->paginate(20)
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
}
