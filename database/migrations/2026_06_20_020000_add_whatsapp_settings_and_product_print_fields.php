<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->boolean('whatsapp_enabled')->default(false)->after('currency');
            $table->string('whatsapp_api_version', 20)->default('v20.0')->after('whatsapp_enabled');
            $table->string('whatsapp_phone_number_id')->nullable()->after('whatsapp_api_version');
            $table->text('whatsapp_access_token')->nullable()->after('whatsapp_phone_number_id');
            $table->string('whatsapp_invoice_base_url')->nullable()->after('whatsapp_access_token');
            $table->text('whatsapp_payment_instructions')->nullable()->after('whatsapp_invoice_base_url');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('material')->nullable()->after('name');
            $table->string('print_size')->nullable()->after('material');
            $table->text('print_notes')->nullable()->after('print_size');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_enabled',
                'whatsapp_api_version',
                'whatsapp_phone_number_id',
                'whatsapp_access_token',
                'whatsapp_invoice_base_url',
                'whatsapp_payment_instructions',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['material', 'print_size', 'print_notes']);
        });
    }
};
