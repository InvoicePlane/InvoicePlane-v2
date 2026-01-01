<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('report_blocks', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->string('block_type');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('filename')->nullable();
            $table->string('width')->default('half'); // half or full
            $table->string('data_source')->default('custom');
            $table->string('default_band')->default('header');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_blocks');
    }
};
