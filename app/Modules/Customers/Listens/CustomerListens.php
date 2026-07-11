<?php

namespace App\Modules\Customers\Listens;

use App\Modules\Customers\Events\CustomerActionEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CustomerListens implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CustomerActionEvent $event): void
    {
        $action = $event->action;
        $data = $event->data;

        // Xử lý logic dựa trên từng hành động (action)
        switch ($action) {
            case 'created':
                Log::info('Khách hàng mới đã được tạo:', ['data' => $data]);
                // TODO: Gọi service gửi Email Welcome, tạo thông báo hệ thống...
                break;

            case 'updated':
                Log::info('Khách hàng đã được cập nhật:', ['data' => $data]);
                break;

            case 'deleted':
                Log::warning('Khách hàng đã bị xóa:', ['data' => $data]);
                // TODO: Xóa các dữ liệu rác liên quan, gửi email chia tay...
                break;

            case 'bulk-deleted':
                Log::warning('Nhiều khách hàng đã bị xóa hàng loạt:', ['data' => $data]);
                break;

            case 'bulk-status-updated':
                Log::info('Trạng thái khách hàng đã được cập nhật hàng loạt:', ['data' => $data]);
                break;

            default:
                Log::info("Sự kiện Customer ({$action}) được kích hoạt:", ['data' => $data]);
                break;
        }
    }
}
