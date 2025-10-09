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
        Schema::create('peppol_integrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('provider_name', 50)->comment('e.g., storecove, e_invoice_be');
            $table->text('encrypted_api_token')->nullable()->comment('Encrypted API credentials');
            $table->json('config')->nullable()->comment('Provider-specific configuration');
            $table->string('test_connection_status', 20)->default('untested')->comment('untested, success, failed');
            $table->text('test_connection_message')->nullable()->comment('Last test connection result message');
            $table->timestamp('test_connection_at')->nullable();
            $table->boolean('enabled')->default(false)->comment('Whether integration is active');
            $table->timestamps();
            
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->index(['company_id', 'enabled']);
            $table->index('provider_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peppol_integrations');
    }
};
