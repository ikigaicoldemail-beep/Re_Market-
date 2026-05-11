<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UploadProfileImageRequest;
use App\Http\Resources\UserResource;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private readonly ProfileService $profileService) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['profile.defaultStore', 'stores']);

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->profileService->update($request->user(), $request->validated());

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => new UserResource($user),
        ]);
    }

    public function uploadAvatar(UploadProfileImageRequest $request): JsonResponse
    {
        $user = $this->profileService->uploadAvatar($request->user(), $request->file('image'));

        return response()->json([
            'message' => 'Avatar uploaded successfully.',
            'user' => new UserResource($user),
        ]);
    }

    public function uploadCover(UploadProfileImageRequest $request): JsonResponse
    {
        $user = $this->profileService->uploadCover($request->user(), $request->file('image'));

        return response()->json([
            'message' => 'Cover image uploaded successfully.',
            'user' => new UserResource($user),
        ]);
    }
}
