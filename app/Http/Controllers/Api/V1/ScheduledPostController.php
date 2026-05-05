<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Social\ScheduleSocialPostRequest;
use App\Http\Requests\Social\UpdateScheduledPostRequest;
use App\Http\Resources\ScheduledPostResource;
use App\Models\ScheduledPost;
use App\Models\SocialPost;
use App\Services\ScheduledPostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduledPostController extends Controller
{
    public function __construct(private readonly ScheduledPostService $scheduledPostService) {}

    public function index(Request $request): JsonResponse
    {
        $scheduledPosts = $this->scheduledPostService->list($request->user());

        return response()->json([
            'scheduled_posts' => ScheduledPostResource::collection($scheduledPosts),
            'meta' => [
                'current_page' => $scheduledPosts->currentPage(),
                'last_page' => $scheduledPosts->lastPage(),
                'per_page' => $scheduledPosts->perPage(),
                'total' => $scheduledPosts->total(),
            ],
        ]);
    }

    public function store(ScheduleSocialPostRequest $request): JsonResponse
    {
        $socialPost = SocialPost::findOrFail($request->validated('social_post_id'));
        $scheduledPost = $this->scheduledPostService->schedule($request->user(), $socialPost, $request->validated());

        return response()->json([
            'message' => 'Social post scheduled successfully.',
            'scheduled_post' => new ScheduledPostResource($scheduledPost),
        ], 201);
    }

    public function show(ScheduledPost $scheduledPost): JsonResponse
    {
        $scheduledPost->load(['socialPost.product.images', 'socialPost.socialAccount']);
        $this->authorize('view', $scheduledPost);

        return response()->json([
            'scheduled_post' => new ScheduledPostResource($scheduledPost),
        ]);
    }

    public function update(UpdateScheduledPostRequest $request, ScheduledPost $scheduledPost): JsonResponse
    {
        $scheduledPost->loadMissing('socialPost');
        $this->authorize('update', $scheduledPost);

        $scheduledPost = $this->scheduledPostService->update($scheduledPost, $request->validated());

        return response()->json([
            'message' => 'Scheduled post updated successfully.',
            'scheduled_post' => new ScheduledPostResource($scheduledPost),
        ]);
    }

    public function destroy(ScheduledPost $scheduledPost): JsonResponse
    {
        $scheduledPost->loadMissing('socialPost');
        $this->authorize('update', $scheduledPost);

        $scheduledPost = $this->scheduledPostService->cancel($scheduledPost);

        return response()->json([
            'message' => 'Scheduled post cancelled successfully.',
            'scheduled_post' => new ScheduledPostResource($scheduledPost),
        ]);
    }
}
