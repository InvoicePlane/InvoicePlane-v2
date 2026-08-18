<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Upgrade merchant_clients table to support both Payments (payment gateways) and Peppol (e-invoicing)
     * with company scoping and encrypted storage.
     */
    public function up(): void
    {
        Schema::table('merchant_clients', function (Blueprint $table): void {
            // Add company_id for company-scoped filtering (nullable to preserve legacy payment rows)
            $table->unsignedBigInteger('company_id')->nullable()->after('id');

            // Add label for human-friendly identification
            $table->string('label')->nullable()->after('driver');

            // Add unique constraint: (company_id, driver, merchant_key) — ensures no duplicate credential keys per provider per company
            $table->unique(['company_id', 'driver', 'merchant_key'], 'unique_merchant_credential_key');

            // Add foreign key on company_id (cascade delete)
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Revert the upgrades.
     */
    public function down(): void
    {
        Schema::table('merchant_clients', function (Blueprint $table): void {
            $table->dropForeign('merchant_clients_company_id_foreign');
            $table->dropUnique('unique_merchant_credential_key');
            $table->dropColumn(['company_id', 'label']);
        });
    }
};
