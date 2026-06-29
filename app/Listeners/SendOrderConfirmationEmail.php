<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Jobs\ProcessOrderInvoicePDF;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationEmail implements ShouldQueue
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
    public function handle(OrderPaid $event): void
    {
        // Giả lập gửi email
        Log::info("Đang gửi email xác nhận đơn hàng cho khách hàng: {$event->order->customer->name} (ID: {$event->order->customer_id})");

        // Logic gửi email thực tế sẽ nằm ở đây
        // Mail::to($event->order->customer->email)->send(new OrderConfirmationMail($event->order));

        // Đẩy job xử lý hóa đơn vào queue
        ProcessOrderInvoicePDF::dispatch($event->order);
    }
}
