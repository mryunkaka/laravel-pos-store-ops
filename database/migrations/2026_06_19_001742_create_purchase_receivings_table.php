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
        Schema::create('purchase_receivings', function (Blueprint $table) {
            $table->id();

            // Reference ke purchase order
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');

            // Reference ke supplier
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');

            // Informasi receiving
            $table->string('receiving_number')->unique();
            $table->date('receiving_date');

            // Status: pending, completed
            $table->enum('status', ['pending', 'completed'])->default('pending');

            // Total
            $table->decimal('sub_total', 12, 2)->default(0);
            $table->decimal('vat', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // Catatan
            $table->text('notes')->nullable();

            // User yang melakukan receiving
            $table->unsignedBigInteger('received_by')->nullable();
            $table->foreign('received_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_receivings');
    }
};
