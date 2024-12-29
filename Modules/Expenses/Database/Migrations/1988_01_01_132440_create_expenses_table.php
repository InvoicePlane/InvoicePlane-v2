<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table): void {
            $table->increments('expense_id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('vendor_id');
            $table->unsignedInteger('invoice_id')->nullable();
            $table->unsignedInteger('category_id');
            $table->unsignedInteger('user_id');
            $table->date('expense_date');
            $table->string('amount');
            $table->string('description')->nullable();

            $table->index('client_id');
            $table->index('vendor_id');
            $table->index('invoice_id');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
