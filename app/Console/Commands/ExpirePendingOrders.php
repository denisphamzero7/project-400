<?php

namespace App\Console\Commands;

use App\Enums\OrdersStatusEnum;
use App\Models\OrderModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpirePendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find pending orders older than 48 hours and set their status to expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to expire old pending orders...');
        Log::info('Scheduler: Running ExpirePendingOrders command.');

        $fortyEightHoursAgo = now()->subHours(48);

        $expiredCount = OrderModel::where('status', OrdersStatusEnum::PENDING)
            ->where('created_at', '<=', $fortyEightHoursAgo)
            ->update(['status' => OrdersStatusEnum::EXPIRED]);

        if ($expiredCount > 0) {
            $this->info("Successfully expired {$expiredCount} orders.");
            Log::info("Scheduler: Expired {$expiredCount} pending orders older than 48 hours.");
        } else {
            $this->info('No pending orders to expire.');
            Log::info('Scheduler: No pending orders to expire.');
        }

        $this->info('Finished expiring old pending orders.');
        return 0;
    }
}
