<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('relations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('primary_contact_id')->nullable();

            $table->string('relation_type', 30);
            $table->string('relation_status', 20)->default('active');
            $table->string('relation_number', 30);
            $table->string('company_name', 150);
            $table->string('trading_name', 70)->nullable();
            $table->string('unique_name')->nullable();
            $table->string('id_number', 70)->nullable();
            $table->string('coc_number', 70)->nullable();
            $table->string('vat_number', 70)->nullable();

            $table->string('currency_code')->nullable();
            $table->string('language')->nullable();
            $table->date('registered_at');

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relations');
    }
};
