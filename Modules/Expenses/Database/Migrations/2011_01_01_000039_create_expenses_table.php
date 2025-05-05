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
            $table->unsignedBigInteger('vendor_id')->nullable()->index('expenses_vendor_id_foreign');
            $table->unsignedBigInteger('customer_id')->nullable()->index('expenses_customer_id_foreign');
            $table->unsignedBigInteger('category_id')->nullable()->index('expenses_category_id_foreign');
            $table->string('expense_number');
            $table->string('expense_status');
            $table->string('expense_type');
            $table->decimal('expense_amount', 20);
            $table->string('description')->nullable();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('vendor_id', 'expenses_vendor_id_foreign')->references('id')->on('relations')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('customer_id', 'expenses_customer_id_foreign')->references('id')->on('relations')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('category_id', 'expenses_category_id_foreign')->references('id')->on('expense_categories')->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
