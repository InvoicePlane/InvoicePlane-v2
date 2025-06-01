<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('document_groups', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('type');
            $table->string('name');
            $table->string('group_identifier_format');
            $table->unsignedBigInteger('next_id');
            $table->unsignedBigInteger('left_pad')->default(0)->index();
            $table->string('format')->nullable();
            $table->unsignedBiginteger('reset_number');
            $table->unsignedBiginteger('last_id');
            $table->unsignedBiginteger('last_year');
            $table->unsignedBiginteger('last_month');
            $table->unsignedBiginteger('last_week');

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_groups');
    }
};
