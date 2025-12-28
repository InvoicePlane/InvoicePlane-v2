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
            $table->unsignedBigInteger('next_id');
            $table->unsignedBigInteger('left_pad')->default(0)->nullable();
            $table->string('format')->nullable();
            $table->string('prefix')->nullable();
            $table->unsignedBigInteger('last_id')->nullable();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numbering');
    }
};
