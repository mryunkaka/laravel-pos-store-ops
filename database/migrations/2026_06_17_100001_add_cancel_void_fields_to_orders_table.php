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
        Schema::table('orders', function (Blueprint $table) {
            $table->text('cancel_reason')->nullable()->after('order_status');
            $table->text('void_reason')->nullable()->after('cancel_reason');
            $table->foreignId('voided_by')->nullable()->after('void_reason')->constrained('users')->onDelete('set null');
            $table->dateTime('voided_at')->nullable()->after('voided_by');
            $table->foreignId('cancelled_by')->nullable()->after('voided_at')->constrained('users')->onDelete('set null');
            $table->dateTime('cancelled_at')->nullable()->after('cancelled_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['voided_by']);
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn([
                'cancel_reason',
                'void_reason',
                'voided_by',
                'voided_at',
                'cancelled_by',
                'cancelled_at',
            ]);
        });
    }
};
