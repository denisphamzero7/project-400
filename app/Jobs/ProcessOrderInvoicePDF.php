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
use Barryvdh\DomPDF\Facade\Pdf;

class ProcessOrderInvoicePDF implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $orderId;

    /**
     * Create a new job instance.
     *
     * @param int $orderId ID của đơn hàng cần xử lý
     */
    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Bắt đầu xử lý xuất hóa đơn PDF cho đơn hàng #{$this->orderId}");

        $order = OrderModel::find($this->orderId);

        if (!$order) {
            Log::warning("Không tìm thấy đơn hàng #{$this->orderId} để xuất hóa đơn PDF.");
            return;
        }

        // Tải các mối quan hệ cần thiết và làm mới model để có dữ liệu mới nhất
        $order->load('customer', 'items.product')->refresh();

        // Tạo PDF từ Blade view (tương tự OrdersService)
        $pdf = Pdf::loadView('pdfs.order_pdf', ['order' => $order]);
        $filename = "invoices/hoa-don-{$order->id}.pdf";

        // Lưu file vào storage/app/public/invoices/
        Storage::disk('public')->put($filename, $pdf->output());

        Log::info("Đã xuất và lưu hóa đơn PDF cho đơn hàng #{$order->id} tại: {$filename}");
    }
}
