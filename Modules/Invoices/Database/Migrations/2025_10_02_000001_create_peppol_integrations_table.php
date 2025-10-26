<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the peppol_integrations table to store PEPPOL integration settings and connection test metadata.
     *
     * The table includes company association (foreign key to companies.id with cascade delete), provider identifier,
     * encrypted API token, last test connection status/message/timestamp, an enabled flag, and indexes for
     * (company_id, enabled) and provider_name.
     */
    public function up(): void
    {
        Schema::create('peppol_integrations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('provider_name', 50)->comment('e.g., e_invoice_be, storecove');
            $table->text('encrypted_api_token')->nullable()->comment('Encrypted API credentials');
            $table->string('test_connection_status', 20)->default('untested')->comment('untested, success, failed');
            $table->text('test_connection_message')->nullable()->comment('Last test connection result message');
            $table->timestamp('test_connection_at')->nullable();
            $table->boolean('enabled')->default(false)->comment('Whether integration is active');
            
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->index(['company_id', 'enabled']);
            $table->index('provider_name');
        });
    }

    /**
     * Drop the `peppol_integrations` table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('peppol_integrations');
    }
};