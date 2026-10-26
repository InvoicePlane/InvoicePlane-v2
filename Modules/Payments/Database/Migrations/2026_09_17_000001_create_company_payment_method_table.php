<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('company_payment_method', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index('company_payment_method_company_id_foreign');
            $table->string('payment_method');

            $table->foreign('company_id', 'company_payment_method_company_id_foreign')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');

            $table->unique(['company_id', 'payment_method'], 'company_payment_method_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_payment_method');
    }
};
