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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('discount', 10, 2)->default(0)->after('selling_price');
            $table->string('discount_type', 10)->default('fixed')->after('discount');
            $table->decimal('wholesale_price', 10, 2)->nullable()->after('discount_type');
            $table->integer('wholesale_qty')->nullable()->after('wholesale_price');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('wholesale_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['discount', 'discount_type', 'wholesale_price', 'wholesale_qty', 'tax_rate']);
        });
    }
};
