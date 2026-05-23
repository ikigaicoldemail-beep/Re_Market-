<?php

namespace App\Notifications;

use App\Models\Store;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewFollower extends Notification
{
    use Queueable;

    public function __construct(public Store $store, public User $follower)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'store.followed',
            'title' => $this->follower->name.' started following your store',
            'body' => $this->store->name,
            'store_id' => $this->store->id,
            'follower_id' => $this->follower->id,
            'url' => '/stores/'.$this->store->id,
        ];
    }
}
