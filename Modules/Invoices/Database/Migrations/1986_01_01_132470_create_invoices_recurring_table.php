<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('invoices_recurring', function (Blueprint $table): void {
            $table->integer('invoice_recurring_id', true);
            $table->integer('invoice_id')->index('inv_recurr_invoice_id');
            $table->date('recur_start_date');
            $table->date('recur_end_date')->nullable();
            $table->string('recur_frequency', 255);
            $table->date('recur_next_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices_recurring');
    }
};
