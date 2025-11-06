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
        Schema::table('products', function (Blueprint $table) {
            $table->string('sheet_name')->nullable()->after('excel_file_name'); // Tên sheet: ICT, MT, HIPOT, FT
            $table->dropUnique(['barcode']); // Xóa unique constraint cũ
            $table->unique(['barcode', 'sheet_name']); // Unique theo cả barcode và sheet
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['barcode', 'sheet_name']);
            $table->unique('barcode');
            $table->dropColumn('sheet_name');
        });
    }
};
