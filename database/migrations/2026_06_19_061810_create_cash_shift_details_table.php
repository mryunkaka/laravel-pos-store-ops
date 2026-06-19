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
        Schema::create('cash_shift_details', function (Blueprint $table) {
            $table->id();

            // Reference ke cash shift
            $table->foreignId('cash_shift_id')->constrained('cash_shifts')->onDelete('cascade');

            // Reference ke transaksi
            $table->foreignId('order_id')->nullable()->constrained('orders');
            $table->enum('transaction_type', ['sale', 'refund', 'void', 'cash_in', 'cash_out'])->default('sale');

            // Data transaksi
            $table->decimal('amount', 15, 2);
            $table->enum('payment_type', ['cash', 'qris', 'debit', 'transfer', 'ewallet'])->default('cash');
            $table->text('description')->nullable();
            $table->timestamp('transaction_time');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_shift_details');
    }
};
