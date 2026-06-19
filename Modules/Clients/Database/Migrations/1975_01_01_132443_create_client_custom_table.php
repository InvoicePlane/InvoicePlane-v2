<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('client_custom', function (Blueprint $table): void {
            $table->integer('client_custom_id', true);
            $table->integer('client_id');
            $table->integer('client_custom_fieldid');
            $table->string('client_custom_fieldvalue')->nullable();

            $table->unique(['client_id', 'client_custom_fieldid'], 'client_custom_client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_custom');
    }
};
