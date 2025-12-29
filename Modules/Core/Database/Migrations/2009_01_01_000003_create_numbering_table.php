<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('numbering', function (Blueprint $table): void {
            $table->id('numbering_id');
            $table->unsignedBigInteger('company_id');
            $table->string('type');
            $table->string('name');
            $table->string('group_identifier_format')->nullable();
            $table->unsignedBigInteger('next_id');
            $table->unsignedBigInteger('left_pad')->default(0)->index();
            $table->string('format')->nullable();
            $table->string('prefix')->nullable();
            $table->unsignedBiginteger('reset_number')->default(0);
            $table->unsignedBiginteger('last_id')->default(0);
            $table->unsignedBiginteger('last_year')->default(0);
            $table->unsignedBiginteger('last_month')->default(0);
            $table->unsignedBiginteger('last_week')->default(0);

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numbering');
    }
};
