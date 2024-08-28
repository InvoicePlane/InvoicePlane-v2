<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('quote_tax_rates', function (Blueprint $table): void {
            $table->integer('quote_tax_rate_id', true);
            $table->integer('quote_id')->index('quote_tax_rates_quote_id');
            $table->integer('tax_rate_id')->index('quote_tax_rates_tax_rate_id');
            $table->integer('include_item_tax')->default(0);
            $table->decimal('quote_tax_rate_amount', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_tax_rates');
    }
};
