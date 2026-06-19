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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            // Reference ke produk
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Tipe pergerakan: in (pembelian/receiving), out (order/retur), adjustment
            $table->enum('type', ['in', 'out', 'adjustment_in', 'adjustment_out']);

            // Reference ke transaksi asal (polymorphic)
            $table->morphs('reference');
            $table->unsignedBigInteger('reference_user_id')->nullable();

            // Detail pergerakan
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2)->default(0);

            // Keterangan & alasan (untuk adjustment)
            $table->text('description')->nullable();

            // Status
            $table->boolean('is_complete')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
