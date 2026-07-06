<?php

namespace Tests\Feature;

use App\Events\OrderPaid;
use App\Jobs\ProcessOrderInvoicePDF;
use App\Listeners\SendOrderConfirmationEmail;
use App\Models\CustomersModel;
use App\Models\OrderModel;
use App\Notifications\OrderConfirmationNotification;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendOrderConfirmationEmailTest extends TestCase
{
    public function test_listener_dispatches_process_order_invoice_pdf_with_correct_order_id(): void
    {
        Bus::fake();
        Notification::fake();

        // Create in-memory model instances without hitting database
        $customer = new CustomersModel();
        $customer->id = 456;
        $customer->name = 'Test Customer';
        $customer->email = 'test@example.com';

        $order = new OrderModel();
        $order->id = 123;
        $order->setRelation('customer', $customer);

        $event = new OrderPaid($order);
        $listener = new SendOrderConfirmationEmail();

        $listener->handle($event);

        // Assert notification was sent to customer
        Notification::assertSentTo(
            $customer,
            OrderConfirmationNotification::class,
            function ($notification, $channels) use ($order) {
                return $notification->order->id === $order->id;
            }
        );

        // Assert ProcessOrderInvoicePDF was dispatched with the correct order ID (integer)
        Bus::assertDispatched(ProcessOrderInvoicePDF::class, function ($job) {
            // Get the orderId property from the job using reflection
            $reflection = new \ReflectionClass($job);
            $property = $reflection->getProperty('orderId');
            $property->setAccessible(true);
            $orderId = $property->getValue($job);

            return $orderId === 123;
        });
    }
}
