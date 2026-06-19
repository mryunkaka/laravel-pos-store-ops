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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();

            // Reference ke produk
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Informasi adjustment
            $table->string('adjustment_number')->unique();
            $table->date('adjustment_date');

            // Tipe adjustment: in (tambahan), out (pengurangan)
            $table->enum('type', ['in', 'out']);

            // Jumlah
            $table->integer('quantity');

            // Alasan
            $table->text('reason');

            // Status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // User yang membuat
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            // User yang menyetujui
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
