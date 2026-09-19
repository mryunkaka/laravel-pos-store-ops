<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const HISTORY_TABLES = [
        'order_details',
        'purchase_order_details',
        'purchase_receiving_details',
        'purchase_return_details',
        'sales_return_details',
        'stock_adjustments',
        'stock_movement_details',
        'stock_movements',
        'stock_opname_details',
        'stock_transfer_details',
    ];

    public function up(): void
    {
        Schema::create('product_references', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->string('material')->nullable();
            $table->string('print_size')->nullable();
            $table->text('print_notes')->nullable();
            $table->string('slug');
            $table->string('code');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('category_name')->nullable();
            $table->integer('stock')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->integer('buying_price')->nullable();
            $table->integer('selling_price')->nullable();
            $table->decimal('discount', 10, 2)->default(0);
            $table->string('discount_type', 10)->default('fixed');
            $table->decimal('wholesale_price', 10, 2)->nullable();
            $table->integer('wholesale_qty')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('image')->nullable();
            $table->date('buying_date')->nullable();
            $table->date('expire_date')->nullable();
            $table->timestamp('source_created_at')->nullable();
            $table->timestamp('source_updated_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        DB::table('products')->orderBy('id')->chunkById(500, function ($products): void {
            $categoryNames = DB::table('categories')
                ->whereIn('id', $products->pluck('category_id')->filter()->all())
                ->pluck('name', 'id')
                ->all();

            $rows = $products->map(function ($product) use ($categoryNames): array {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'material' => $product->material,
                    'print_size' => $product->print_size,
                    'print_notes' => $product->print_notes,
                    'slug' => $product->slug,
                    'code' => $product->code,
                    'category_id' => $product->category_id,
                    'category_name' => $categoryNames[$product->category_id] ?? null,
                    'stock' => $product->stock,
                    'minimum_stock' => $product->minimum_stock,
                    'buying_price' => $product->buying_price,
                    'selling_price' => $product->selling_price,
                    'discount' => $product->discount,
                    'discount_type' => $product->discount_type,
                    'wholesale_price' => $product->wholesale_price,
                    'wholesale_qty' => $product->wholesale_qty,
                    'tax_rate' => $product->tax_rate,
                    'image' => $product->image,
                    'buying_date' => $product->buying_date,
                    'expire_date' => $product->expire_date,
                    'source_created_at' => $product->created_at,
                    'source_updated_at' => $product->updated_at,
                    'archived_at' => $product->deleted_at,
                    'deleted_at' => $product->deleted_at,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            })->all();

            if ($rows !== []) {
                DB::table('product_references')->insert($rows);
            }
        });

        foreach (self::HISTORY_TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropForeign(['product_id']);
                $table->foreign('product_id')
                    ->references('id')
                    ->on('product_references')
                    ->restrictOnDelete();
            });
        }

        $nextProductId = ((int) DB::table('product_references')->max('id')) + 1;
        DB::statement('ALTER TABLE products AUTO_INCREMENT = ' . $nextProductId);
    }

    public function down(): void
    {
        // Historical references must remain intact after deployment.
    }
};
