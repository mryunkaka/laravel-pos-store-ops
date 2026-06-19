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
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();

            // Reference ke purchase receiving
            $table->foreignId('purchase_receiving_id')->constrained('purchase_receivings')->onDelete('cascade');

            // Reference ke supplier
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');

            // Reference ke user yang membuat
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->string('return_number')->unique();
            $table->date('return_date');
            $table->text('description')->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
