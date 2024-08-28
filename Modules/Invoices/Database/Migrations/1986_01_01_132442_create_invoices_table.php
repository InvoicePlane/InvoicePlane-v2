<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->increments('invoice_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('invoice_group_id');
            $table->tinyInteger('invoice_status_id')->default(1)->index('invoice_status_id');
            $table->boolean('is_read_only')->nullable();
            $table->string('invoice_password', 90)->nullable();
            $table->date('invoice_date_created');
            $table->time('invoice_time_created')->default('00:00:00');
            $table->dateTime('invoice_date_modified');
            $table->date('invoice_date_due');
            $table->string('invoice_number', 100)->nullable();
            $table->decimal('invoice_discount_amount', 20)->nullable();
            $table->decimal('invoice_discount_percent', 20)->nullable();
            $table->longText('invoice_terms');
            $table->char('invoice_url_key', 32)->unique('invoice_url_key');
            $table->unsignedInteger('payment_method')->default(0);
            $table->unsignedInteger('creditinvoice_parent_id')->nullable();

            $table->index(['user_id', 'client_id', 'invoice_group_id', 'invoice_date_created', 'invoice_date_due', 'invoice_number'], 'invoices_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
