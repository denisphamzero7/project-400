<?php

namespace App\Jobs;

use App\Models\OrderModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessOrderInvoicePDF implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public OrderModel $order;

    /**
     * Create a new job instance.
     */
    public function __construct(OrderModel $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Bắt đầu xử lý xuất hóa đơn PDF cho đơn hàng #{$this->order->id}");

        // Giả lập quá trình tạo PDF (có thể mất thời gian)
        sleep(5); // Giả lập job nặng

        $filename = "invoices/invoice_{$this->order->id}.txt";
        $content = "Đây là nội dung hóa đơn cho đơn hàng #{$this->order->id}.";

        // Lưu file vào storage/app/public/invoices/
        Storage::disk('public')->put($filename, $content);

        Log::info("Đã xuất và lưu hóa đơn PDF cho đơn hàng #{$this->order->id} tại: {$filename}");
    }
}
