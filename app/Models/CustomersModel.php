<?php

namespace App\Models;

use App\Enums\CustomersStatusEnum;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'loyalty_points',
        'status',
    ];

    protected $casts = [
        'status' => CustomersStatusEnum::class,
    ];


    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function (Builder $query, string $search) {
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
        });
    }

    public function orders(): HasMany
    {
      return $this->hasMany(OrderModel::class, 'customer_id');
    }

}
