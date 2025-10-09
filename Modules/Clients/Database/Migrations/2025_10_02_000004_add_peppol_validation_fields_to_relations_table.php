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
        Schema::table('relations', function (Blueprint $table) {
            $table->string('peppol_scheme', 50)->nullable()->after('peppol_id')
                ->comment('Peppol endpoint scheme (e.g., BE:CBE, DE:VAT)');
            
            $table->string('peppol_validation_status', 20)->nullable()->after('enable_e_invoicing')
                ->comment('Quick lookup: valid, invalid, not_found, error, null');
            
            $table->text('peppol_validation_message')->nullable()->after('peppol_validation_status')
                ->comment('Last validation result message');
            
            $table->timestamp('peppol_validated_at')->nullable()->after('peppol_validation_message')
                ->comment('When was the Peppol ID last validated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('relations', function (Blueprint $table) {
            $table->dropColumn(['peppol_scheme', 'peppol_validation_status', 'peppol_validation_message', 'peppol_validated_at']);
        });
    }
};
