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
            $table->unsignedBigInteger('quote_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->date('added_at')->nullable();
            $table->string('item_name')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->decimal('quantity', 20, 2);
            $table->decimal('price', 20, 2);
            $table->decimal('discount', 20, 2)->default(0);
            $table->decimal('subtotal', 20, 2);
            $table->unsignedBigInteger('tax_rate_id')->nullable();
            $table->unsignedMediumInteger('order')->nullable();
            $table->string('description')->nullable();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('quote_id')->references('id')->on('quotes')->onDelete('set null');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('set null');
            $table->foreign('unit_id')->references('id')->on('product_units')->onDelete('set null');
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
