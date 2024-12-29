<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table): void {
            $table->increments('unit_id');
            $table->string('unit_name', 50);
            $table->string('unit_name_plrl', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
