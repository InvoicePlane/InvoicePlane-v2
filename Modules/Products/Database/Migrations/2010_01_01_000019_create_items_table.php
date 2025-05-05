<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->unsignedBigInteger('tax_rate_id')->nullable();
            $table->string('type');
            $table->string('code');
            $table->string('item_name');
            $table->decimal('price', 20, 2);
            $table->decimal('cost_price', 20, 2)->nullable();
            $table->integer('tariff')->nullable();
            $table->string('description')->nullable();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('item_categories')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('product_units')->onDelete('set null');
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
