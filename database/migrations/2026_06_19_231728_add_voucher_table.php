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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Kode voucher unik');
            $table->string('name', 100)->comment('Nama voucher');
            $table->text('description')->nullable()->comment('Deskripsi voucher');
            $table->enum('type', ['percentage', 'fixed'])->default('fixed')->comment('Jenis diskon');
            $table->decimal('discount', 10, 2)->default(0)->comment('Nilai diskon');
            $table->integer('min_purchase')->default(0)->comment('Minimal pembelian');
            $table->integer('max_discount')->nullable()->comment('Maksimal diskon (untuk percentage)');
            $table->date('start_date')->comment('Tanggal mulai aktif');
            $table->date('end_date')->comment('Tanggal berakhir');
            $table->integer('max_use')->nullable()->comment('Maksimal penggunaan');
            $table->integer('used_count')->default(0)->comment('Jumlah sudah dipakai');
            $table->boolean('is_active')->default(true)->comment('Status voucher');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
