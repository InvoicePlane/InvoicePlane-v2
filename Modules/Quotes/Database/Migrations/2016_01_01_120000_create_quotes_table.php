<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table): void {
            $table->integer('quote_id', true);
            $table->integer('invoice_id')->default(0)->index('quotes_invoice_id');
            $table->integer('user_id');
            $table->integer('client_id');
            $table->integer('invoice_group_id');
            $table->tinyInteger('quote_status_id')->default(1)->index('quote_status_id');
            $table->date('quote_date_created');
            $table->dateTime('quote_date_modified');
            $table->date('quote_date_expires');
            $table->string('quote_number', 100)->nullable();
            $table->decimal('quote_discount_amount', 20)->nullable();
            $table->decimal('quote_discount_percent', 20)->nullable();
            $table->char('quote_url_key', 32);
            $table->string('quote_password', 90)->nullable();
            $table->longText('notes')->nullable();

            $table->index(['user_id', 'client_id', 'invoice_group_id', 'quote_date_created', 'quote_date_expires', 'quote_number'], 'quotes_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
