<?php

namespace App\Modules\Customers\Exports;

use App\Models\CustomersModel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomersExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected array $filters = []
        // Đã gỡ bỏ $organizationId vì trong CustomerModel hiện tại không có trường này
    ) {}

    /**
     * Xuất danh sách khách hàng.
     */
    public function collection()
    {
        // Khởi tạo query và áp dụng bộ lọc từ scopeFilter
        // Đã gỡ bỏ with(['creator', 'editor']) vì CustomerModel không có 2 relationship này
        $customers = CustomersModel::query()
            ->filter($this->filters)
            ->get();

        // Map dữ liệu khớp với các fillable trong CustomerModel
        return $customers->map(fn ($customer) => [
            'id'             => $customer->id,
            'name'           => $customer->name,
            'email'          => $customer->email,
            'loyalty_points' => $customer->loyalty_points,
            'status'         => $customer->status?->value ?? $customer->status ?? 'N/A', // Bắt value từ CustomersStatusEnum
            'created_at'     => $customer->created_at?->format('d/m/Y H:i:s'),
            'updated_at'     => $customer->updated_at?->format('d/m/Y H:i:s'),
        ]);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tên khách hàng',
            'Email',
            'Điểm thành viên (Loyalty Points)',
            'Trạng thái',
            'Ngày tạo',
            'Ngày cập nhật',
        ];
    }
}
