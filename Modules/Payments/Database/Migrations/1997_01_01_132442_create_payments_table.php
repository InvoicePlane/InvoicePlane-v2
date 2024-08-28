<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->increments('payment_id');
            $table->unsignedInteger('invoice_id')->index('payments_invoice_id');
            $table->unsignedInteger('payment_method_id')->default(0)->index('payment_method_id');
            $table->date('payment_date');
            $table->decimal('payment_amount', 20)->nullable()->index('payment_amount');
            $table->longText('payment_note');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
