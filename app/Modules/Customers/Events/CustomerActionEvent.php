<?php

namespace App\Modules\Customers\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerActionEvent implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Đẩy vào hàng đợi ưu tiên cao để cập nhật UI nhanh
    public $queue = 'instant';

    public function __construct(public string $action, public mixed $data)
    {
    }

    public function broadcastOn(): array
    {
        // Phát sự kiện trên kênh chung của customers
        return [new Channel('customers-channel')];
    }

    public function broadcastAs(): string
    {
        return 'CustomerEvent';
    }
}
