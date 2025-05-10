<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('expense_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('expense_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->date('added_at')->nullable();
            $table->string('item_name')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->decimal('quantity', 20, 4)->default(1.00);
            $table->decimal('price', 20, 4)->default(0.00);
            $table->decimal('discount', 20, 4)->nullable()->default(0.00);
            $table->decimal('subtotal', 20, 4);
            $table->decimal('tax_1', 20, 4)->nullable()->default(0.00);
            $table->decimal('tax_2', 20, 4)->nullable()->default(0.00);
            $table->decimal('tax', 20, 4)->nullable()->default(0.00);
            $table->decimal('total', 20, 4)->nullable()->default(0.00);
            $table->unsignedBigInteger('tax_rate_id')->nullable();
            $table->unsignedBigInteger('tax_rate_2_id')->nullable();
            $table->unsignedMediumInteger('display_order')->nullable();
            $table->string('description')->nullable();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('expense_id')->references('id')->on('expenses')->onDelete('set null');
            $table->foreign('item_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('unit_id')->references('id')->on('product_units')->onDelete('set null');
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('set null');
            $table->foreign('tax_rate_2_id', 'fk_expense_items_tax_rate_2_id')->references('id')->on('tax_rates')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('line_items');
    }
};
