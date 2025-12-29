<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('customer_id')->index('invoices_relation_id_foreign');
            $table->unsignedBigInteger('numbering_id')->nullable();
            $table->unsignedBigInteger('creditinvoice_parent_id')->nullable()->index('invoices_creditinvoice_parent_id_foreign');
            $table->unsignedBigInteger('user_id')->index('invoices_user_id_foreign');

            $table->string('invoice_number');
            $table->string('invoice_status');
            $table->enum('invoice_sign', ['1', '-1'])->default('1');
            $table->date('invoiced_at')->nullable();
            $table->date('invoice_due_at')->nullable();
            $table->decimal('invoice_discount_amount', 20, 4)->default(0);
            $table->decimal('invoice_discount_percent', 20);
            $table->decimal('item_tax_total', 20, 4);
            $table->decimal('invoice_item_subtotal', 20);
            $table->decimal('invoice_tax_total', 20);
            $table->decimal('invoice_total', 20);
            $table->string('invoice_password')->nullable();
            $table->string('url_key', 32)->nullable();
            $table->boolean('is_read_only')->nullable()->default(false);

            $table->string('template')->nullable();
            $table->string('summary')->nullable();
            $table->text('terms')->nullable();
            $table->text('footer')->nullable();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('customer_id', 'invoices_relation_id_foreign')
                ->references('id')
                ->on('relations')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('numbering_id', 'invoices_numbering_id_foreign')
                ->references('id')
                ->on('numbering')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('user_id', 'invoices_user_id_foreign')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
