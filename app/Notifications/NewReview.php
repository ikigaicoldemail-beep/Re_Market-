<?php

namespace App\Notifications;

use App\Models\ProductReview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReview extends Notification
{
    use Queueable;

    public function __construct(public ProductReview $review)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $product = $this->review->product;
        return [
            'type' => 'product.reviewed',
            'title' => 'New '.$this->review->rating.'★ review on "'.($product?->title ?? 'your product').'"',
            'body' => $this->review->title ?: ($this->review->body ? mb_substr($this->review->body, 0, 120) : ''),
            'product_id' => $this->review->product_id,
            'review_id' => $this->review->id,
            'url' => '/products/'.$this->review->product_id,
        ];
    }
}
