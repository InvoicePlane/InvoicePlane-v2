<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('company_email_template', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index('company_email_template_company_id_foreign');
            $table->unsignedBigInteger('email_template_id')->index('company_email_template_email_template_id_foreign');

            $table->foreign('company_id', 'company_email_template_company_id_foreign')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('email_template_id', 'company_email_template_email_template_id_foreign')->references('id')->on('email_templates')->onUpdate('cascade')->onDelete('cascade');

            $table->unique(['company_id', 'email_template_id'], 'company_email_template_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_email_template');
    }
};
