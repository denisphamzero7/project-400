<?php

namespace App\Observers;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use Illuminate\Support\Facades\DB;

class OrderItemObserver
{
    /**
     * Handle the OrderItemModel "created" event.
     */
    public function created(OrderItemModel $orderItem): void
    {
        $this->updateOrderTotalAmount($orderItem->order);
    }

    /**
     * Handle the OrderItemModel "updated" event.
     */
    public function updated(OrderItemModel $orderItem): void
    {
        $this->updateOrderTotalAmount($orderItem->order);
    }

    /**
     * Handle the OrderItemModel "deleted" event.
     */
    public function deleted(OrderItemModel $orderItem): void
    {
        // Trước khi item bị xóa hoàn toàn, chúng ta vẫn có thể truy cập vào order của nó
        $this->updateOrderTotalAmount($orderItem->order);
    }

    /**
     * Recalculate and update the total amount for the given order.
     *
     * @param OrderModel|null $order
     */
    protected function updateOrderTotalAmount(?OrderModel $order): void
    {
        // Nếu không có order (trường hợp hiếm) thì không làm gì cả
        if (!$order) {
            return;
        }

        // Tải lại tất cả các item của order và tính tổng: sum(price * quantity)
        $totalAmount = $order->items()->sum(DB::raw('price * quantity'));

        $order->update(['total_amount' => $totalAmount]);
    }
}