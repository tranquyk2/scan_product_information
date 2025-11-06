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
            $table->string('barcode')->unique()->index(); // Mã vạch (unique)
            $table->string('model_name'); // Tên model sản phẩm
            $table->integer('quantity')->default(0); // Số lượng
            $table->string('excel_file_name')->nullable(); // Tên file Excel nguồn
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
