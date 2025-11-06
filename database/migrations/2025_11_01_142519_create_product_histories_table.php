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
        Schema::create('product_histories', function (Blueprint $table) {
            $table->id();
            $table->string('model_name'); // Tên model (EPCD05CA1B, ...)
            $table->integer('quantity'); // Số lượng thay thế
            $table->date('replacement_date'); // Ngày thay thế
            $table->string('excel_file_name')->nullable(); // File nguồn
            $table->string('process')->nullable(); // Công đoạn
            $table->string('management_code')->nullable(); // Mã số quản lý
            $table->string('executor')->nullable(); // Người thực hiện
            $table->string('confirm')->nullable(); // Xác nhận
            $table->string('note')->nullable(); // Ghi chú
            $table->timestamps();
            $table->index('model_name'); // Index để tìm kiếm nhanh
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_histories');
    }
};
