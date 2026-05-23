<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Requests\Chat\StartConversationRequest;
use App\Http\Resources\ChatMessageResource;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\Product;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(private readonly ChatService $chatService) {}

    public function index(Request $request): JsonResponse
    {
        $conversations = $this->chatService->listConversations($request->user());

        return response()->json([
            'conversations' => ConversationResource::collection($conversations),
            'meta' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
            ],
        ]);
    }

    public function store(StartConversationRequest $request): JsonResponse
    {
        $recipient = User::findOrFail($request->validated('recipient_user_id'));
        $product = $request->validated('product_id')
            ? Product::findOrFail($request->validated('product_id'))
            : null;

        $conversation = $this->chatService->startConversation($request->user(), $recipient, $product);

        return response()->json([
            'message' => 'Conversation ready.',
            'conversation' => new ConversationResource($conversation),
        ], 201);
    }

    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $messages = $this->chatService->listMessages($conversation);

        return response()->json([
            'messages' => ChatMessageResource::collection($messages),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    public function send(SendMessageRequest $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('sendMessage', $conversation);

        $message = $this->chatService->sendMessage(
            $request->user(),
            $conversation,
            $request->validated(),
            $request->file('attachment'),
        );

        return response()->json([
            'message' => 'Message sent successfully.',
            'chat_message' => new ChatMessageResource($message),
        ], 201);
    }

    public function markAsSeen(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $conversation = $this->chatService->markAsSeen($request->user(), $conversation);

        return response()->json([
            'message' => 'Conversation marked as seen.',
            'conversation' => new ConversationResource($conversation),
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'unread_count' => $this->chatService->unreadCount($request->user()),
        ]);
    }
}
