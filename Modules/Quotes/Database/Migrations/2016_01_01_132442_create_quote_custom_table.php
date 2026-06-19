<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('quote_custom', function (Blueprint $table): void {
            $table->integer('quote_custom_id', true);
            $table->integer('quote_id');
            $table->integer('quote_custom_fieldid');
            $table->string('quote_custom_fieldvalue')->nullable();

            $table->unique(['quote_id', 'quote_custom_fieldid'], 'quote_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_custom');
    }
};
