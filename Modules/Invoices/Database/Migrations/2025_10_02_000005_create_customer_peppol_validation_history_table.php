<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the customer_peppol_validation_history table to record Peppol identifier validation events for customers.
     *
     * The table stores customer, integration and user references, the Peppol scheme and identifier, validation status and message, timestamps, foreign key constraints, and indexes for efficient lookups.
     */
    public function up(): void
    {
        Schema::create('customer_peppol_validation_history', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('integration_id')->nullable()->comment('Which integration was used for validation');
            $table->unsignedBigInteger('validated_by')->nullable()->comment('User who triggered validation');
            $table->string('peppol_scheme', 50);
            $table->string('peppol_id', 100);
            $table->string('validation_status', 20)->comment('valid, invalid, not_found, error');
            $table->text('validation_message')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            
            $table->foreign('customer_id')->references('id')->on('relations')->onDelete('cascade');
            $table->foreign('integration_id')->references('id')->on('peppol_integrations')->onDelete('set null');
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['customer_id', 'created_at']);
            $table->index('validation_status');
        });
    }

    /**
     * Reverts the migration by removing the customer_peppol_validation_history table.
     *
     * Drops the table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_peppol_validation_history');
    }
};