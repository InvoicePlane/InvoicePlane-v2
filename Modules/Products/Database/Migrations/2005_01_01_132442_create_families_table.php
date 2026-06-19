<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table): void {
            $table->increments('family_id');
            $table->string('family_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
