<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('company_numbering', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index('company_numbering_company_id_foreign');
            $table->unsignedBigInteger('numbering_id')->index('company_numbering_numbering_id_foreign');

            $table->foreign('company_id', 'company_numbering_company_id_foreign')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('numbering_id', 'company_numbering_numbering_id_foreign')->references('id')->on('numbering')->onUpdate('cascade')->onDelete('cascade');

            $table->unique(['company_id', 'numbering_id'], 'company_numbering_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_numbering');
    }
};
