<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Social\ConnectSocialAccountRequest;
use App\Http\Resources\SocialAccountResource;
use App\Models\SocialAccount;
use App\Services\SocialAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialAccountController extends Controller
{
    public function __construct(private readonly SocialAccountService $socialAccountService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'social_accounts' => SocialAccountResource::collection(
                $this->socialAccountService->list($request->user())
            ),
        ]);
    }

    public function store(ConnectSocialAccountRequest $request): JsonResponse
    {
        $account = $this->socialAccountService->connect($request->user(), $request->validated());

        return response()->json([
            'message' => 'Social account connected successfully.',
            'social_account' => new SocialAccountResource($account),
        ], 201);
    }

    public function destroy(SocialAccount $socialAccount): JsonResponse
    {
        $this->authorize('update', $socialAccount);

        $account = $this->socialAccountService->disconnect($socialAccount);

        return response()->json([
            'message' => 'Social account disconnected successfully.',
            'social_account' => new SocialAccountResource($account),
        ]);
    }
}
