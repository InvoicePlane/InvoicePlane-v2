<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('upload_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('upload_id');
            $table->string('upload_detail_key');
            $table->string('upload_detail_value');

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('upload_id')->references('id')->on('uploads')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_details');
    }
};
