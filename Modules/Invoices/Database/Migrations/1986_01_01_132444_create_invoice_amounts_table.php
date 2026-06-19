<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('invoice_amounts', function (Blueprint $table): void {
            $table->integer('invoice_amount_id', true);
            $table->integer('invoice_id')->index('uniq_invoice_id');
            $table->enum('invoice_sign', ['1', '-1'])->default('1');
            $table->decimal('invoice_item_subtotal', 20)->nullable();
            $table->decimal('invoice_item_tax_total', 20)->nullable();
            $table->decimal('invoice_tax_total', 20)->nullable();
            $table->decimal('invoice_total', 20)->nullable();
            $table->decimal('invoice_paid', 20)->nullable();
            $table->decimal('invoice_balance', 20)->nullable();

            $table->index(['invoice_paid', 'invoice_balance'], 'invoice_paid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_amounts');
    }
};
