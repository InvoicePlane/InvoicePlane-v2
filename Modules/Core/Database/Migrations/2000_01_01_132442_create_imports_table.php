<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('imports', function (Blueprint $table): void {
            $table->increments('import_id');
            $table->dateTime('import_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imports');
    }
};
