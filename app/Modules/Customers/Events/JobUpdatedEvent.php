<?php
namespace App\Modules\Jobs\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // Đổi lại thành ShouldBroadcast
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// SỬ DỤNG ShouldBroadcast VÀ ShouldQueue
class JobUpdatedEvent implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Chỉ định ném vào queue ưu tiên cao nhất
    public $queue = 'instant';

    public function __construct(public string $action, public mixed $data)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('jobs-realtime-channel')
        ];
    }

    public function broadcastAs(): string
    {
        return 'JobEvent';
    }
}
