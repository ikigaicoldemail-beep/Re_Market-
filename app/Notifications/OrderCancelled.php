<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderCancelled extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order.cancelled',
            'title' => 'Order #'.$this->order->order_number.' was cancelled',
            'body' => 'The buyer cancelled this order before payment.',
            'order_id' => $this->order->id,
            'url' => '/orders/'.$this->order->id,
        ];
    }
}
