<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('invoice_transactions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->boolean('is_successful')->default(false);
            $table->string('transaction_reference')->nullable();
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_transactions');
    }
};
