<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('quote_item_amounts', function (Blueprint $table): void {
            $table->integer('item_amount_id', true);
            $table->integer('item_id')->index('quote_item_amounts_item_id');
            $table->decimal('item_subtotal', 20)->nullable();
            $table->decimal('item_tax_total', 20)->nullable();
            $table->decimal('item_discount', 20)->nullable();
            $table->decimal('item_total', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_item_amounts');
    }
};
