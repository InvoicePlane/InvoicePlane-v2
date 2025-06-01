<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('type');
            $table->string('code')->nullable();
            $table->string('product_name')->nullable();
            $table->decimal('price', 20, 4)->nullable();
            $table->decimal('cost_price', 20, 4)->nullable();
            $table->unsignedBigInteger('tax_rate_id')->nullable()->default(0)->index('tax_rate_id');
            $table->unsignedBigInteger('tax_rate_2_id')->nullable()->default(0)->index('tax_rate_2_id');
            $table->unsignedBigInteger('product_tariff')->nullable();
            $table->longText('description')->nullable();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('product_units')->onDelete('set null');

            $table->foreign('tax_rate_id', 'fk_item_lookups_tax_rate_id')->references('id')->on('tax_rates')->onDelete('cascade');
            $table->foreign('tax_rate_2_id', 'fk_item_lookups_tax_rate_2_id')->references('id')->on('tax_rates')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
