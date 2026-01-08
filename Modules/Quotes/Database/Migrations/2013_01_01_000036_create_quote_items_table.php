<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('quote_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('quote_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->unsignedBigInteger('product_unit_id')->nullable();
            $table->date('added_at')->nullable();
            $table->string('item_name')->nullable();
            $table->string('product_unit', 50)->comment('for legacy reasons')->nullable();
            $table->boolean('is_recurring')->comment('nullable for legacy reasons')->nullable()->default(false);
            $table->decimal('quantity', 20, 8)->nullable()->default(1.00);
            $table->decimal('price', 20, 4)->nullable()->default(0.00);
            $table->decimal('discount', 20, 4)->nullable()->default(0.00);
            $table->decimal('subtotal', 20, 4)->nullable()->default(0.00);
            $table->decimal('tax_1', 20, 4)->nullable()->default(0.00);
            $table->decimal('tax_2', 20, 4)->nullable()->default(0.00);
            $table->decimal('tax_total', 20, 4)->nullable()->default(0.00);
            $table->decimal('total', 20, 4)->nullable()->default(0.00);
            $table->unsignedBigInteger('tax_rate_id')->nullable();
            $table->unsignedBigInteger('tax_rate_2_id')->nullable();
            $table->unsignedBigInteger('display_order')->default(0);
            $table->longText('description')->nullable();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('quote_id')->references('id')->on('quotes')->onDelete('restrict');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('product_unit_id')->references('id')->on('product_units')->onDelete('set null');
            $table->foreign('tax_rate_id', 'fk_quote_items_tax_rate_id')->references('id')->on('tax_rates')->onDelete('set null');
            $table->foreign('tax_rate_2_id', 'fk_quote_items_tax_rate_2_id')->references('id')->on('tax_rates')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
