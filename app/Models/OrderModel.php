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
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
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
