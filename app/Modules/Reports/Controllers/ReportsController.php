<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reports\Service\ReportsService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    use RespondsWithJson;

    public function __construct(private ReportsService $reportsService)
    {
    }

    /**
     * Generate and return a complex revenue report.
     */
    public function revenue(Request $request)
    {
        try {
            $report = $this->reportsService->generateRevenueReport();
            return $this->success($report, 'Báo cáo doanh thu đã được tạo thành công.');
        } catch (\Throwable $th) {
            return $this->error('Tạo báo cáo thất bại!', 500, null, $th->getMessage());
        }
    }
}
