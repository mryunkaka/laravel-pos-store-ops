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
            $table->decimal('discount', 10, 2)->default(0)->after('sub_total');
            $table->string('discount_type', 10)->default('fixed')->after('discount');
            $table->decimal('service_charge', 10, 2)->default(0)->after('discount_type');
            $table->decimal('tax_total', 10, 2)->default(0)->after('service_charge');
            $table->string('tax_type', 10)->default('inclusive')->after('tax_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['discount', 'discount_type', 'service_charge', 'tax_total', 'tax_type']);
        });
    }
};
