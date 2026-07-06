<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Jobs\ProcessOrderInvoicePDF;
use App\Notifications\OrderConfirmationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'notifications';

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
    public function handle(OrderPaid $event): void
    {
        $event->order->customer->notify(new OrderConfirmationNotification($event->order));

        // Đẩy job xử lý hóa đơn vào queue
        ProcessOrderInvoicePDF::dispatch($event->order->id);

        Log::info("Đã gửi thông báo xác nhận đơn hàng thành công cho đơn hàng ID: {$event->order->id}");
    }
}
