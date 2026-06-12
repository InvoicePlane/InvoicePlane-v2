<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('fieldable_type');
            $table->string('custom_field_label', 50)->nullable();
            $table->string('field_type')->comment('CustomFieldType Enum!')->default('TEXT');
            $table->unsignedMediumInteger('field_order')->default(0);

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
