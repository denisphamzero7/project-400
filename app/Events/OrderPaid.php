<?php

namespace App\Events;

use App\Models\OrderModel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public OrderModel $order;

    /**
     * Create a new event instance.
     */
    public function __construct(OrderModel $order)
    {
        $this->order = $order;
    }
}
