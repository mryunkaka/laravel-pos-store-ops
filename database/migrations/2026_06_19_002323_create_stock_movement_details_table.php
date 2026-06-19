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
        Schema::create('stock_movement_details', function (Blueprint $table) {
            $table->id();

            // Reference ke stock movement
            $table->foreignId('stock_movement_id')->constrained()->onDelete('cascade');

            // Reference ke produk
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Detail pergerakan
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->integer('change_quantity');

            // Catatan
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movement_details');
    }
};
