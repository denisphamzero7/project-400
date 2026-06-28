<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->decimal('price', 15, 2)->default(0);
            $table->unsignedInteger('stock_quantity')->default(0);

            // SỬ DỤNG ENUM: Chỉ cho phép lưu các giá trị được chỉ định trong mảng.
            // Sẽ quăng lỗi Database ngay lập tức nếu code cố tình lưu một trạng thái lạ.
            $table->enum('status', ['active', 'inactive', 'draft'])
                  ->default('active')
                  ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
