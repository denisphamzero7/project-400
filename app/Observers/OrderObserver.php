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
        // Total amount will be calculated in the service after items are created.
    }

    /**
     * Handle the OrderModel "updating" event.
     *
     * @param  \App\Models\OrderModel  $order
     * @return void
     */
    public function updating(OrderModel $order): void
    {
        if ($order->isDirty('status') && $order->status === OrdersStatusEnum::CANCELLED->value) {
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
        //
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
