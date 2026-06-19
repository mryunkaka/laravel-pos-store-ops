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
        Schema::create('cash_closing_details', function (Blueprint $table) {
            $table->id();

            // Reference ke cash closing
            $table->foreignId('cash_closing_id')->constrained('cash_closings')->onDelete('cascade');

            // Reference ke shift
            $table->foreignId('cash_shift_id')->nullable()->constrained('cash_shifts');

            // Data shift detail dalam closing
            $table->decimal('shift_sales', 15, 2)->default(0);
            $table->decimal('shift_cash', 15, 2)->default(0);
            $table->decimal('shift_non_cash', 15, 2)->default(0);
            $table->decimal('shift_void', 15, 2)->default(0);
            $table->decimal('shift_refund', 15, 2)->default(0);

            // Cash counting per shift
            $table->decimal('shift_cash_expected', 15, 2)->default(0);
            $table->decimal('shift_cash_actual', 15, 2)->default(0);
            $table->decimal('shift_cash_difference', 15, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_closing_details');
    }
};
