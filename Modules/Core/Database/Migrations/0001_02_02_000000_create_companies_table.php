<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('companies', static function (Blueprint $table): void {
            $table->id();
            $table->string('search_code', 10)->unique();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('vat_number')->nullable();
            $table->string('id_number')->nullable();
            $table->string('coc_number')->nullable();
            $table->string('logo')->nullable();
            $table->string('quote_template')->nullable();
            $table->string('invoice_template')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
