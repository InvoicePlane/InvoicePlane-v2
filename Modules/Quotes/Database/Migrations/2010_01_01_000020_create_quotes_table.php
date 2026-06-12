<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('numbering_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('quote_number')->nullable()->index('quote_number');
            $table->string('quote_status');
            $table->date('quoted_at')->nullable();
            $table->date('quote_expires_at')->nullable();
            $table->decimal('quote_discount_amount', 20, 4)->default(0.00);
            $table->decimal('quote_discount_percent', 20);
            $table->decimal('item_tax_total', 20)->nullable()->default(0.00);
            $table->decimal('quote_item_subtotal', 20, 4);
            $table->decimal('quote_tax_total', 20);
            $table->decimal('quote_total', 20);
            $table->string('quote_password')->nullable();
            $table->string('url_key', 32)->nullable();
            $table->string('template')->nullable();
            $table->string('summary')->nullable();
            $table->text('terms')->nullable();
            $table->text('footer')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('prospect_id', 'quotes_prospect_id_foreign')->references('id')->on('relations')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('numbering_id', 'quotes_numbering_id_foreign')
                ->references('id')
                ->on('numbering')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->foreign('user_id', 'quotes_user_id_foreign')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
