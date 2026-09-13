<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('relations', function (Blueprint $table) {
            $table->string('peppol_id', 100)->nullable()->after('vat_number')
                ->comment('Peppol participant identifier (e.g., BE:0123456789)');

            $table->string('peppol_format', 50)->nullable()->after('peppol_id')
                ->comment('Preferred Peppol document format (matches PeppolDocumentFormat values)');

            $table->boolean('enable_e_invoicing')->default(false)->after('peppol_format')
                ->comment('Whether e-invoicing via Peppol is enabled for this customer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('relations', function (Blueprint $table) {
            $table->dropColumn(['peppol_id', 'peppol_format', 'enable_e_invoicing']);
        });
    }
};
