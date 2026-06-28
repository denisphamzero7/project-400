<?php

namespace App\Models;

// Sửa lại use, bạn đang use chính class OrderModel hiện tại là không cần thiết nếu nó cùng namespace, 
// nhưng để chắc chắn, tôi cứ giữ lại hoặc bạn có thể bỏ đi.
use App\Models\OrderModel; 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductModel extends Model
{
    use HasFactory;

    protected $table = 'products';
    
    // Đã bổ sung trường 'status' vào fillable
    protected $fillable = [
        'name',
        'price',
        'stock_quantity',
        'status' 
    ];

    // Chú ý: Từ khóa đúng là $casts (có chữ s), của bạn đang viết là $cast
    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer'
        // Bạn có thể cast thêm 'status' => ProductStatusEnum::class nếu muốn
    ];

    /**
     * Scope dùng để lọc (Filter) danh sách Sản phẩm dựa trên tham số truyền vào
     */
    public function scopeFilter(Builder $query, array $filters = []): void
    {
        // 1. Tìm kiếm theo tên sản phẩm
        $query->when($filters['search'] ?? null, function (Builder $query, string $search) {
            $query->where('name', 'like', '%' . $search . '%');
        });

        // 2. Lọc theo trạng thái
        $query->when($filters['status'] ?? null, function (Builder $query, string $status) {
            $query->where('status', $status);
        });

        // 3. Lọc theo khoảng giá (Từ giá - Đến giá)
        $query->when($filters['min_price'] ?? null, function (Builder $query, $minPrice) {
            $query->where('price', '>=', $minPrice);
        });

        $query->when($filters['max_price'] ?? null, function (Builder $query, $maxPrice) {
            $query->where('price', '<=', $maxPrice);
        });

        // 4. Lọc số lượng tồn kho (Ví dụ: sắp hết hàng)
        $query->when(isset($filters['low_stock']), function (Builder $query) {
             // Lọc các sản phẩm có tồn kho dưới 10
            $query->where('stock_quantity', '<', 10);
        });

        // 5. Logic Sắp xếp (Sort)
        $query->when($filters['sort_by'] ?? 'id', function ($query, $sortBy) use ($filters) {
            $allowed = ['id', 'name', 'price', 'stock_quantity', 'status', 'created_at'];
            $column = in_array($sortBy, $allowed) ? $sortBy : 'id';

            $order = $filters['sort_order'] ?? 'desc';
            $sortOrder = in_array(strtolower($order), ['asc', 'desc']) ? $order : 'desc';

            $query->orderBy($column, $sortOrder);
        });
    }

    /**
     * Mối quan hệ N-N: Một sản phẩm có thể nằm trong nhiều đơn hàng.
     * Thông qua bảng trung gian là 'order_items'.
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(OrderModel::class, 'order_items')
                    ->withPivot('quantity', 'price') // Cho phép lấy thêm cột từ bảng trung gian
                    ->withTimestamps();
    }
}