<?php
namespace App\Models;
use App\Models\OrderModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductModel extends Model
{
    use HasFactory;

    protected $table ='products';
    protected $fillable =[
        'name',
        'price',
        'stock_quantity',
    ];

    protected $cast =[
        'price'=>'decimal:2',
        'stock_quantity'=>'integer'
    ];

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