<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('title');
            $table->string('type');
            $table->string('subject')->nullable();
            $table->longText('body');
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
