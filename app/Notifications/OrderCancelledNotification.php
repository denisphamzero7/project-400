<?php

namespace App\Notifications;

use App\Models\OrderModel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class OrderCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public OrderModel $order
    ) {
        $this->queue = 'notifications';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Đơn hàng của bạn đã bị hủy #' . $this->order->id)
                    ->greeting('Xin chào ' . $this->order->customer->name . ',')
                    ->line('Chúng tôi rất tiếc phải thông báo rằng đơn hàng của bạn đã bị hủy.')
                    ->line('Dưới đây là thông tin chi tiết đơn hàng đã hủy của bạn:')
                    ->line('Mã đơn hàng: #' . $this->order->id)
                    ->line('Tổng tiền: ' . number_format($this->order->total_amount, 0, ',', '.') . ' VNĐ')
                    ->action('Xem chi tiết đơn hàng', env('FRONTEND_URL', 'http://localhost:8000') . '/orders/' . $this->order->id)
                    ->line('Nếu bạn không yêu cầu hủy đơn hàng này hoặc cần hỗ trợ thêm, vui lòng liên hệ với bộ phận chăm sóc khách hàng của chúng tôi.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'total_amount' => $this->order->total_amount,
            'customer_name' => $this->order->customer->name,
        ];
    }
}
