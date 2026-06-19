<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->foreignId('order_id')->after('id')->constrained('orders')->cascadeOnDelete();
            $table->string('return_number')->after('order_id')->unique();
            $table->date('return_date')->after('return_number');
            $table->enum('return_type', ['refund', 'exchange'])->after('return_date')->default('refund');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->after('return_type')->default('pending');
            $table->decimal('refund_amount', 15, 2)->after('status')->default(0);
            $table->text('reason')->after('refund_amount')->nullable();
            $table->foreignId('created_by')->after('reason')->constrained('users');
            $table->foreignId('completed_by')->after('created_by')->nullable()->constrained('users');
            $table->timestamp('completed_at')->after('completed_by')->nullable();
            $table->softDeletes();
        });

        Schema::table('sales_return_details', function (Blueprint $table) {
            $table->foreignId('sales_return_id')->after('id')->constrained('sales_returns')->cascadeOnDelete();
            $table->foreignId('order_detail_id')->after('sales_return_id')->constrained('order_details');
            $table->foreignId('product_id')->after('order_detail_id')->constrained('products');
            $table->integer('quantity')->after('product_id');
            $table->enum('condition', ['sellable', 'damaged'])->after('quantity')->default('sellable');
            $table->decimal('unit_price', 15, 2)->after('condition')->default(0);
            $table->decimal('total', 15, 2)->after('unit_price')->default(0);
            $table->text('notes')->after('total')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('sales_return_details', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeign(['sales_return_id']);
            $table->dropForeign(['order_detail_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn([
                'sales_return_id',
                'order_detail_id',
                'product_id',
                'quantity',
                'condition',
                'unit_price',
                'total',
                'notes',
            ]);
        });

        Schema::table('sales_returns', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeign(['order_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['completed_by']);
            $table->dropColumn([
                'order_id',
                'return_number',
                'return_date',
                'return_type',
                'status',
                'refund_amount',
                'reason',
                'created_by',
                'completed_by',
                'completed_at',
            ]);
        });
    }
};
