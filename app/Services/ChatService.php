<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChatService
{
    public function startConversation(User $user, User $recipient, ?Product $product = null): Conversation
    {
        if ($user->id === $recipient->id) {
            throw ValidationException::withMessages([
                'recipient_user_id' => ['You cannot start a conversation with yourself.'],
            ]);
        }

        if ($product && ! in_array($recipient->id, [$product->user_id, $user->id], true)) {
            throw ValidationException::withMessages([
                'recipient_user_id' => ['The selected recipient is not valid for the given product context.'],
            ]);
        }

        $conversation = Conversation::query()
            ->where('type', 'private')
            ->when($product, fn ($query) => $query->where('product_id', $product->id), fn ($query) => $query->whereNull('product_id'))
            ->whereHas('participants', fn ($query) => $query->where('user_id', $user->id))
            ->whereHas('participants', fn ($query) => $query->where('user_id', $recipient->id))
            ->with(['product.images', 'participants.user.profile', 'lastMessage.sender'])
            ->first();

        if ($conversation) {
            return $this->appendUnreadCounts($user, collect([$conversation]))->first();
        }

        $conversation = DB::transaction(function () use ($user, $recipient, $product) {
            $conversation = Conversation::create([
                'product_id' => $product?->id,
                'created_by' => $user->id,
                'type' => 'private',
            ]);

            $conversation->participants()->createMany([
                [
                    'user_id' => $user->id,
                    'joined_at' => now(),
                ],
                [
                    'user_id' => $recipient->id,
                    'joined_at' => now(),
                ],
            ]);

            return $conversation;
        });

        return $conversation->load(['product.images', 'participants.user.profile', 'lastMessage.sender']);
    }

    public function listConversations(User $user): LengthAwarePaginator
    {
        $conversations = Conversation::query()
            ->with(['product.images', 'participants.user.profile', 'lastMessage.sender'])
            ->whereHas('participants', fn ($query) => $query->where('user_id', $user->id))
            ->orderByDesc('last_message_at')
            ->paginate(15);

        $conversations->setCollection($this->appendUnreadCounts($user, $conversations->getCollection()));

        return $conversations;
    }

    public function listMessages(Conversation $conversation): LengthAwarePaginator
    {
        return $conversation->messages()
            ->with('sender.profile')
            ->latest('created_at')
            ->paginate(30);
    }

    public function sendMessage(User $user, Conversation $conversation, array $data): ChatMessage
    {
        return DB::transaction(function () use ($user, $conversation, $data) {
            $message = $conversation->messages()->create([
                'sender_id' => $user->id,
                'type' => $data['type'] ?? 'text',
                'body' => $data['body'],
                'sent_at' => now(),
            ]);

            $conversation->update([
                'last_message_id' => $message->id,
                'last_message_at' => $message->sent_at,
            ]);

            $conversation->participants()
                ->where('user_id', $user->id)
                ->update([
                    'last_read_message_id' => $message->id,
                    'last_read_at' => now(),
                ]);

            return $message->load('sender.profile');
        });
    }

    public function markAsSeen(User $user, Conversation $conversation): Conversation
    {
        $lastMessage = $conversation->messages()->latest('id')->first();

        $conversation->participants()
            ->where('user_id', $user->id)
            ->update([
                'last_read_message_id' => $lastMessage?->id,
                'last_read_at' => now(),
            ]);

        return $conversation->fresh(['product.images', 'participants.user.profile', 'lastMessage.sender']);
    }

    public function unreadCount(User $user): int
    {
        $participantRows = $user->conversations()
            ->withPivot('last_read_message_id')
            ->get();

        $count = 0;

        foreach ($participantRows as $conversation) {
            $lastReadMessageId = $conversation->pivot->last_read_message_id;

            $count += $conversation->messages()
                ->when($lastReadMessageId, fn ($query) => $query->where('id', '>', $lastReadMessageId))
                ->where('sender_id', '!=', $user->id)
                ->count();
        }

        return $count;
    }

    private function appendUnreadCounts(User $user, $conversations)
    {
        return $conversations->map(function (Conversation $conversation) use ($user) {
            $participant = $conversation->participants->firstWhere('user_id', $user->id);
            $lastReadMessageId = $participant?->last_read_message_id;

            $participant->unread_count = $conversation->messages()
                ->when($lastReadMessageId, fn ($query) => $query->where('id', '>', $lastReadMessageId))
                ->where('sender_id', '!=', $user->id)
                ->count();

            return $conversation;
        });
    }
}
