<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('invoice_sumex', function (Blueprint $table): void {
            $table->integer('sumex_id', true);
            $table->integer('sumex_invoice');
            $table->integer('sumex_reason');
            $table->string('sumex_diagnosis', 500);
            $table->string('sumex_observations', 500);
            $table->date('sumex_treatmentstart');
            $table->date('sumex_treatmentend');
            $table->date('sumex_casedate');
            $table->string('sumex_casenumber', 35)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_sumex');
    }
};
