<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->string('company_name')->nullable()->after('work_order');
            $table->string('company_vat_number')->nullable()->after('company_name');
            $table->string('company_id_number')->nullable()->after('company_vat_number');
            $table->string('company_coc_number')->nullable()->after('company_id_number');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn(['company_name', 'company_vat_number', 'company_id_number', 'company_coc_number']);
        });
    }
};
