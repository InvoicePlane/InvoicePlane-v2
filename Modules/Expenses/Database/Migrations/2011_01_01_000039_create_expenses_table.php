<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('invoice_id')->index()->nullable();
            $table->unsignedBigInteger('customer_id')->nullable()->index('fk_expenses_customer_id');
            $table->unsignedBigInteger('vendor_id')->nullable()->index('fk_expenses_vendor_id');
            $table->unsignedBigInteger('category_id')->nullable()->index('fk_expenses_category_id');
            $table->unsignedBigInteger('user_id')->nullable()->index('fk_expenses_user_id');
            $table->string('expense_number');
            $table->string('expense_status');
            $table->string('expense_type');
            $table->date('expensed_at');
            $table->decimal('expense_amount', 20, 4);
            $table->string('description')->nullable();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('invoice_id', 'fk_expenses_invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('customer_id', 'fk_expenses_customer_id')->references('id')->on('relations')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('vendor_id', 'fk_expenses_vendor_id')->references('id')->on('relations')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('category_id', 'fk_expenses_category_id')->references('id')->on('expense_categories')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('user_id', 'fk_expenses_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
