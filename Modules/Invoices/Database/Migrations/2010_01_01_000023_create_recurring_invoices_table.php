<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('recurring_invoices', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBiginteger('customer_id')->index();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('document_group_id')->nullable()->index('recurr_document_group_id_foreign');
            $table->string('frequency');
            $table->date('start_at');
            $table->date('end_at')->nullable();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('customer_id', 'fk_recurring_invoices_customer_id')->references('id')->on('relations')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('document_group_id', 'recurr_document_group_id_foreign')
                ->references('id')
                ->on('document_groups')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_invoices');
    }
};
