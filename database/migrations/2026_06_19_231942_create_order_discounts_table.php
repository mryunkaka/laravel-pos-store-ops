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
        Schema::create('order_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->string('type', 20)->comment('item|invoice|promo|voucher');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('product_id untuk item, voucher_id untuk promo');
            $table->string('reference_type')->nullable()->comment('App\\Models\\Product|App\\Models\\Voucher');
            $table->decimal('amount', 10, 2)->comment('Jumlah diskon');
            $table->string('description', 255)->nullable()->comment('Deskripsi diskon');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_discounts');
    }
};
