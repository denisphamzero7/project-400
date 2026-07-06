<?php

namespace App\Observers;

use App\Enums\OrdersStatusEnum;
use App\Models\OrderModel;
use App\Models\ProductModel;

class OrderObserver
{
    /**
     * Handle the OrderModel "creating" event.
     *
     * @param  \App\Models\OrderModel  $order
     * @return void
     */
    public function creating(OrderModel $order): void
    {
        // This will be handled in the service/controller layer during creation
    }

    /**
     * Handle the OrderModel "created" event.
     *
     * @param  \App\Models\OrderModel  $order
     * @return void
     */
    public function created(OrderModel $order): void
    {
        // Tải lại mối quan hệ items nếu chưa có
        $order->loadMissing('items');

        // Tính tổng tiền từ các order items
        $totalAmount = $order->items->reduce(function ($carry, $item) {
            return $carry + ($item->price * $item->quantity);
        }, 0);

        // Cập nhật tổng tiền của đơn hàng mà không kích hoạt lại event
        if ($order->total_amount !== $totalAmount) {
            $order->total_amount = $totalAmount;
            // Sử dụng withoutEvents để tránh vòng lặp vô tận khi save
            OrderModel::withoutEvents(function () use ($order) {
                $order->save();
            });
        }

        // Sau khi tổng tiền được cập nhật, kiểm tra và bắn event BigOrderPlaced
        if ($order->total_amount > 10000000) {
            broadcast(new \App\Events\BigOrderPlaced($order));
        }
    }

    /**
     * Handle the OrderModel "updating" event.
     *
     * @param  \App\Models\OrderModel  $order
     * @return void
     */
    public function updating(OrderModel $order): void
    {
        if ($order->isDirty('status') && $order->status === OrdersStatusEnum::CANCELLED) {
            foreach ($order->items as $item) {
                $product = ProductModel::find($item->product_id);
                if ($product) {
                    $product->increment('stock_quantity', $item->quantity);
                }
            }
        }
    }

    /**
     * Handle the OrderModel "updated" event.
     *
     * @param  \App\Models\OrderModel  $order
     * @return void
     */
    public function updated(OrderModel $order): void
    {
        if ($order->wasChanged('status')) {
            if ($order->status === OrdersStatusEnum::COMPLETED) {
                event(new \App\Events\OrderPaid($order));
            } elseif ($order->status === OrdersStatusEnum::CANCELLED) {
                $order->customer->notify(new \App\Notifications\OrderCancelledNotification($order));
            }
        }
    }

    /**
     * Handle the OrderModel "deleted" event.
     *
     * @param  \App\Models\OrderModel  $order
     * @return void
     */
    public function deleted(OrderModel $order): void
    {
        //
    }

    /**
     * Handle the OrderModel "restored" event.
     *
     * @param  \App\Models\OrderModel  $order
     * @return void
     */
    public function restored(OrderModel $order): void
    {
        //
    }

    /**
     * Handle the OrderModel "force deleted" event.
     *
     * @param  \App\Models\OrderModel  $order
     * @return void
     */
    public function forceDeleted(OrderModel $order): void
    {
        //
    }
}
