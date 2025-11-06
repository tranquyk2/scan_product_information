<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('product_histories', function (Blueprint $table) {
			$table->string('process')->nullable(); // Công đoạn
			$table->string('management_code')->nullable(); // Mã số quản lý
			$table->string('executor')->nullable(); // Người thực hiện
			$table->string('confirm')->nullable(); // Xác nhận
			$table->string('note')->nullable(); // Ghi chú
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('product_histories', function (Blueprint $table) {
			$table->dropColumn(['process', 'management_code', 'executor', 'confirm', 'note']);
		});
	}
};
