<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table): void {
            $table->increments('email_template_id');
            $table->string('email_template_title');
            $table->string('email_template_type');
            $table->longText('email_template_body');
            $table->string('email_template_subject');
            $table->string('email_template_from_name');
            $table->string('email_template_from_email');
            $table->string('email_template_cc')->nullable();
            $table->string('email_template_bcc')->nullable();
            $table->string('email_template_pdf_template')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
