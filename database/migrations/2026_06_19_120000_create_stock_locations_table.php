<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_locations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('stock_locations')->insert([
            [
                'code' => 'MAIN',
                'name' => 'Toko Utama',
                'is_default' => true,
                'is_active' => true,
                'notes' => 'Lokasi stok default untuk struktur awal transfer.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'STORE',
                'name' => 'Gudang Toko',
                'is_default' => false,
                'is_active' => true,
                'notes' => 'Lokasi tambahan awal dalam satu toko.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_locations');
    }
};
