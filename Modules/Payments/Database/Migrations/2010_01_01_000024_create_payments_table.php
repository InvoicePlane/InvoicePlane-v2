<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('merchant_client_id')->nullable();
            $table->string('payment_method');
            $table->string('payment_status');
            $table->date('paid_at')->nullable();
            $table->decimal('payment_amount', 20, 4);
            $table->text('notes')->nullable();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('customer_id')->references('id')->on('relations')->restrictOnDelete();
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
            // $table->foreign('merchant_client_id')->references('id')->on('merchant_clients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
