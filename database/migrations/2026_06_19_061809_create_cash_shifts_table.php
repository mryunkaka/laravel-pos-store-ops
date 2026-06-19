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
        Schema::create('cash_shifts', function (Blueprint $table) {
            $table->id();

            // Reference ke kasir
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Reference ke outlet (untuk multi-locasi di masa depan)
            $table->unsignedBigInteger('location_id')->default(1);

            // Data shift
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->decimal('total_cash', 15, 2)->default(0);
            $table->decimal('total_non_cash', 15, 2)->default(0);
            $table->decimal('total_void', 15, 2)->default(0);
            $table->decimal('total_refund', 15, 2)->default(0);

            // Status
            $table->enum('status', ['active', 'closed', 'cancelled'])->default('active');

            // Supervisor approval
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('closing_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_shifts');
    }
};
