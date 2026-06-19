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
        Schema::create('cash_closings', function (Blueprint $table) {
            $table->id();

            // Reference ke outlet
            $table->unsignedBigInteger('location_id')->default(1);

            // Data closing
            $table->date('closing_date');
            $table->timestamp('closing_time');
            $table->timestamp('closed_at')->nullable();

            // Summary
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->decimal('total_cash', 15, 2)->default(0);
            $table->decimal('total_non_cash', 15, 2)->default(0);
            $table->decimal('total_void', 15, 2)->default(0);
            $table->decimal('total_refund', 15, 2)->default(0);
            $table->decimal('total_due', 15, 2)->default(0);

            // Cash counting
            $table->decimal('cash_expected', 15, 2)->default(0);
            $table->decimal('cash_actual', 15, 2)->default(0);
            $table->decimal('cash_difference', 15, 2)->default(0);

            // Status
            $table->enum('status', ['closed', 'verified', 'cancelled'])->default('closed');

            // Supervisor approval
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_closings');
    }
};
