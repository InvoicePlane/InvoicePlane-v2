<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('product_inventories', function (Blueprint $table): void {
            $table->increments('inventory_id');
            $table->unsignedInteger('product_id');
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('reorder_point')->nullable();

            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_inventories');
    }
};
