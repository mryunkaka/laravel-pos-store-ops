<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->decimal('buying_price', 10, 2)->default(0)->after('unit_price');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->integer('minimum_stock')->default(0)->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('buying_price');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('minimum_stock');
        });
    }
};
