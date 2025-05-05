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
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('payment_method_id')->index('payments_payment_method_id_foreign');
            $table->string('payment_status');
            $table->date('paid_at')->nullable()->default(null);
            $table->decimal('payment_amount', 20);

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('invoice_id', 'payments_invoice_id_foreign')
                ->references('id')
                ->on('invoices')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->foreign('payment_method_id', 'payments_payment_method_id_foreign')
                ->references('id')
                ->on('payment_methods')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
