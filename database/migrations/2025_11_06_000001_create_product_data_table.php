<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_data', function (Blueprint $table) {
            $table->id();
            $table->string('model_name');
            $table->integer('quantity')->default(0);
            $table->date('replacement_date')->nullable();
            $table->string('excel_file_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_data');
    }
};
