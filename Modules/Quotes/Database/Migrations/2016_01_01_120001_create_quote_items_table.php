<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('quote_items', function (Blueprint $table): void {
            $table->integer('item_id', true)->index('quote_items_item_id');
            $table->integer('quote_id');
            $table->integer('item_tax_rate_id')->index('quote_items_item_tax_rate_id');
            $table->integer('item_product_id')->nullable();
            $table->date('item_date_added');
            $table->string('item_name')->nullable();
            $table->string('item_description')->nullable();
            $table->decimal('item_quantity', 20)->nullable();
            $table->decimal('item_price', 20)->nullable();
            $table->decimal('item_discount_amount', 20)->nullable();
            $table->integer('item_order')->default(0);
            $table->string('item_product_unit', 50)->nullable();
            $table->integer('item_unit_id')->nullable();

            $table->index(['quote_id', 'item_date_added', 'item_order'], 'quote_items_quote_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
