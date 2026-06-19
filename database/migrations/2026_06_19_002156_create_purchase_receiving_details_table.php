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
        Schema::create('purchase_receiving_details', function (Blueprint $table) {
            $table->id();

            // Reference ke purchase receiving
            $table->foreignId('purchase_receiving_id')->constrained()->onDelete('cascade');

            // Reference ke purchase order detail
            $table->foreignId('purchase_order_detail_id')->constrained()->onDelete('cascade');

            // Reference ke produk
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Detail receiving
            $table->integer('received_quantity');
            $table->integer('rejected_quantity')->default(0);

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
        Schema::dropIfExists('purchase_receiving_details');
    }
};
