<?php

namespace App\Models;

use App\Enums\OrdersStatusEnum;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CustomersModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderModel extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'customer_id',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'status' => OrdersStatusEnum::class,
    ];


    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function (Builder $query, string $search) {
            $query->where(function (Builder $query) use ($search) {
                $query->where('id', 'like', '%' . $search . '%')
                    ->orWhereHas('customer', function (Builder $query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        })
        ->when($filters['status'] ?? null, function (Builder $query, string $status) {
            $query->where('status', $status);
        })
        ->when($filters['from_date'] ?? null, function (Builder $query, string $fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        })
        ->when($filters['to_date'] ?? null, function (Builder $query, string $toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        })
        ->when($filters['price_from'] ?? null, function (Builder $query, string $priceFrom) {
            $query->where('total_amount', '>=', $priceFrom);
        })
        ->when($filters['price_to'] ?? null, function (Builder $query, string $priceTo) {
            $query->where('total_amount', '<=', $priceTo);
        })
        ->when($filters['sort_by'] ?? 'created_at', function (Builder $query, string $sortBy) use ($filters) {
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);
        });
    }

    /**
     * Mối quan hệ N-1: Đơn hàng thuộc về 1 khách hàng cụ thể
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomersModel::class, 'customer_id');
    }

    /**
     * Lấy ra danh sách các chi tiết đơn hàng (các dòng dữ liệu trong bảng order_items)
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }

    /**
     * Truy vấn trực tiếp ra các Sản phẩm nằm trong đơn hàng này (Bỏ qua bước trung gian)
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(ProductModel::class, 'order_items')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

}
