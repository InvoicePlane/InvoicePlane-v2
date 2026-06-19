<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->integer('item_id', true);
            $table->integer('invoice_id');
            $table->integer('item_tax_rate_id')->default(0);
            $table->integer('item_product_id')->nullable();
            $table->date('item_date_added');
            $table->integer('item_task_id')->nullable();
            $table->string('item_name')->nullable();
            $table->longText('item_description')->nullable();
            $table->decimal('item_quantity', 10);
            $table->decimal('item_price', 20)->nullable();
            $table->decimal('item_discount_amount', 20)->nullable();
            $table->integer('item_order')->default(0);
            $table->boolean('item_is_recurring')->nullable();
            $table->string('item_product_unit', 50)->nullable();
            $table->integer('item_unit_id')->nullable();
            $table->date('item_date')->nullable();

            $table->index(['invoice_id', 'item_tax_rate_id', 'item_date_added', 'item_order'], 'inv_item_uniq_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
