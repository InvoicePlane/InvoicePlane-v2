<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('invoice_tax_rates', function (Blueprint $table): void {
            $table->integer('invoice_tax_rate_id', true);
            $table->integer('invoice_id');
            $table->integer('tax_rate_id');
            $table->integer('include_item_tax')->default(0);
            $table->decimal('invoice_tax_rate_amount', 10)->default(0);

            $table->index(['invoice_id', 'tax_rate_id'], 'inv_tax_rates_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_tax_rates');
    }
};
