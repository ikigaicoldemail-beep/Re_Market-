<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Social\CreateSocialPostRequest;
use App\Http\Requests\Social\ShareProductRequest;
use App\Http\Resources\SharedProductResource;
use App\Http\Resources\SocialPostResource;
use App\Models\Product;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Services\SocialPostingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialPostController extends Controller
{
    public function __construct(private readonly SocialPostingService $socialPostingService) {}

    public function index(Request $request): JsonResponse
    {
        $posts = $request->user()
            ->socialPosts()
            ->with(['product.images', 'socialAccount'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'social_posts' => SocialPostResource::collection($posts),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    public function store(CreateSocialPostRequest $request): JsonResponse
    {
        $product = Product::findOrFail($request->validated('product_id'));
        $account = SocialAccount::findOrFail($request->validated('social_account_id'));

        $post = $this->socialPostingService->createPost($request->user(), $product, $account, $request->validated());

        return response()->json([
            'message' => 'Social post created successfully.',
            'social_post' => new SocialPostResource($post),
        ], 201);
    }

    public function show(SocialPost $socialPost): JsonResponse
    {
        $this->authorize('view', $socialPost);

        $socialPost->load(['product.images', 'socialAccount']);

        return response()->json([
            'social_post' => new SocialPostResource($socialPost),
        ]);
    }

    public function publish(SocialPost $socialPost): JsonResponse
    {
        $this->authorize('update', $socialPost);

        $socialPost = $this->socialPostingService->publish($socialPost);

        return response()->json([
            'message' => 'Social post published successfully.',
            'social_post' => new SocialPostResource($socialPost),
        ]);
    }

    public function share(ShareProductRequest $request): JsonResponse
    {
        $product = Product::findOrFail($request->validated('product_id'));
        $sharedProduct = $this->socialPostingService->shareProduct($request->user(), $product, $request->validated());

        return response()->json([
            'message' => 'Product shared successfully.',
            'shared_product' => new SharedProductResource($sharedProduct),
        ], 201);
    }
}
