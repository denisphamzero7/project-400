<?php

namespace App\Modules\Orders\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderActionEvent implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Đẩy vào hàng đợi ưu tiên cao để cập nhật UI nhanh
    public $queue = 'instant';

    public function __construct(public string $action, public mixed $data)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('orders-channel')];
    }

    public function broadcastAs(): string
    {
        return 'OrderEvent';
    }
}
