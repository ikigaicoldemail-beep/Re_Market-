<?php

namespace App\Notifications;

use App\Models\ChatMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewChatMessage extends Notification
{
    use Queueable;

    public function __construct(public ChatMessage $message)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $sender = $this->message->sender;
        $preview = (string) ($this->message->body ?? '');
        if (mb_strlen($preview) > 120) {
            $preview = mb_substr($preview, 0, 117).'...';
        }

        return [
            'type' => 'chat.message',
            'title' => 'New message from '.($sender?->name ?? 'someone'),
            'body' => $preview === '[image]' ? '📷 Sent an image' : $preview,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'url' => '/messages/'.$this->message->conversation_id,
        ];
    }
}
