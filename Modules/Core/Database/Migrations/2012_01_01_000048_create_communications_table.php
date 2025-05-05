<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('communications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->morphs('communicationable');
            $table->boolean('is_primary')->default(false);
            $table->string('contactable_type');
            $table->string('contactable_value');

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};
