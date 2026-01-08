<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('tax_rate_type'); // TaxRateType Enum
            $table->boolean('is_active')->default(true);
            $table->string('code');
            $table->string('name');
            $table->boolean('is_compound')->default(0);
            $table->boolean('calculate_vat')->default(0);
            $table->decimal('rate', 5, 2)->default(0.00);

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
