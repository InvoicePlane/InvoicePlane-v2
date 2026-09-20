<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('company_tax_rate', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index('company_tax_rate_company_id_foreign');
            $table->unsignedBigInteger('tax_rate_id')->index('company_tax_rate_tax_rate_id_foreign');

            $table->foreign('company_id', 'company_tax_rate_company_id_foreign')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('tax_rate_id', 'company_tax_rate_tax_rate_id_foreign')->references('id')->on('tax_rates')->onUpdate('cascade')->onDelete('cascade');

            $table->unique(['company_id', 'tax_rate_id'], 'company_tax_rate_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_tax_rate');
    }
};
