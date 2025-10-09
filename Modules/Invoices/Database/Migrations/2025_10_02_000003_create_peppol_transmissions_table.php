<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peppol_transmissions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('integration_id');
            $table->string('format', 50)->comment('Document format used (e.g., peppol_bis_3.0, ubl_2.1)');
            $table->string('status', 20)->default('pending')->comment('pending, queued, processing, sent, accepted, rejected, failed, retrying, dead');
            $table->unsignedInteger('attempts')->default(0);
            $table->string('idempotency_key', 64)->unique()->comment('Hash to prevent duplicate transmissions');
            $table->string('external_id')->nullable()->comment('Provider transaction/document ID');
            $table->string('stored_xml_path')->nullable()->comment('Path to stored XML file');
            $table->string('stored_pdf_path')->nullable()->comment('Path to stored PDF file');
            $table->text('last_error')->nullable()->comment('Last error message if failed');
            $table->string('error_type', 20)->nullable()->comment('TRANSIENT, PERMANENT, UNKNOWN');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('relations')->onDelete('cascade');
            $table->foreign('integration_id')->references('id')->on('peppol_integrations')->onDelete('cascade');
            
            $table->index(['invoice_id', 'integration_id']);
            $table->index('status');
            $table->index('external_id');
            $table->index('next_retry_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peppol_transmissions');
    }
};
