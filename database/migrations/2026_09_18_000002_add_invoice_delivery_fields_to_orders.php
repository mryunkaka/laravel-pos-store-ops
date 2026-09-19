<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('invoice_pdf_path')->nullable()->after('due_amount');
            $table->string('invoice_upload_status', 20)->nullable()->after('invoice_pdf_path')->index();
            $table->string('invoice_file_id')->nullable()->after('invoice_upload_status');
            $table->text('invoice_url')->nullable()->after('invoice_file_id');
            $table->dateTime('invoice_expires_at')->nullable()->after('invoice_url');
            $table->dateTime('invoice_generated_at')->nullable()->after('invoice_expires_at');
            $table->dateTime('invoice_uploaded_at')->nullable()->after('invoice_generated_at');
            $table->text('invoice_error')->nullable()->after('invoice_uploaded_at');
        });
    }

    public function down(): void
    {
        // Intentionally no-op. Invoice fields are additive and must survive rollback attempts.
    }
};
