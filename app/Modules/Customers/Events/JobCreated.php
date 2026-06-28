<?php

namespace App\Modules\Jobs\Events;

use App\Modules\Jobs\Models\ITJob; // Khai báo Model Job của bạn
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $job;

    // Nhận toàn bộ dữ liệu của Job vừa tạo vào đây
    public function __construct(ITJob $job)
    {
        $this->job = $job;
    }

    public function broadcastOn(): array
    {
        // Bắn vào kênh của tổ chức chứa công việc này
        return [
            new Channel('jobs.organization.' . $this->job->organization_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'job.created';
    }
}
