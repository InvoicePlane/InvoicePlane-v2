<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->increments('product_id');
            $table->unsignedInteger('family_id')->nullable();
            $table->string('product_sku')->nullable();
            $table->string('product_name')->nullable();
            $table->longText('product_description');
            $table->decimal('product_price', 20)->nullable();
            $table->decimal('purchase_price', 20)->nullable();
            $table->string('provider_name')->nullable();
            $table->unsignedInteger('tax_rate_id')->nullable();
            $table->unsignedInteger('unit_id')->nullable();
            $table->integer('product_tariff')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
