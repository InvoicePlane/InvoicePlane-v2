<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('recurring_invoice_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBiginteger('recurring_invoice_id')->index();
            $table->unsignedBiginteger('item_id')->index(); // LOOKUP!
            $table->unsignedBiginteger('tax_rate_id')->index()->default(0);
            $table->unsignedBiginteger('tax_rate_2_id')->index()->default(0);
            $table->string('name');
            $table->decimal('quantity', 20, 4);
            $table->decimal('price', 20, 4);

            $table->decimal('subtotal', 20, 4);
            $table->decimal('tax_1', 20, 4);
            $table->decimal('tax_2', 20, 4);
            $table->decimal('tax', 20, 4);
            $table->decimal('total', 20, 4);

            $table->unsignedBiginteger('display_order')->index()->default(0);

            $table->text('description');

            $table->foreign('recurring_invoice_id', 'fk_recurring_invoice_items_recurring_invoice_id')->references('id')->on('recurring_invoices')->onDelete('cascade');
            $table->foreign('tax_rate_id', 'fk_recurring_invoice_items_tax_rate_id')->references('id')->on('tax_rates')->onDelete('cascade');
            $table->foreign('tax_rate_2_id', 'fk_recurring_invoice_items_tax_rate_2_id')->references('id')->on('tax_rates')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_invoice_items');
    }
};
