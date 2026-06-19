<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('quote_amounts', function (Blueprint $table): void {
            $table->integer('quote_amount_id', true);
            $table->integer('quote_id')->index('quote_amounts_quote_id');
            $table->decimal('quote_item_subtotal', 20)->nullable();
            $table->decimal('quote_item_tax_total', 20)->nullable();
            $table->decimal('quote_tax_total', 20)->nullable();
            $table->decimal('quote_total', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_amounts');
    }
};
