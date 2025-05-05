<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name')->index();
            $table->string('symbol');
            $table->string('placement');
            $table->string('decimal');
            $table->string('thousands');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
