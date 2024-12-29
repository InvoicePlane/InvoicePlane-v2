<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table): void {
            $table->increments('category_id');
            $table->string('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};
