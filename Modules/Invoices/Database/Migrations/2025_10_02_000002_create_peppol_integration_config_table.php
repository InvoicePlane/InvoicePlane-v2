<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peppol_integration_config', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('integration_id');
            $table->string('config_key', 100);
            $table->text('config_value');
            
            $table->foreign('integration_id')->references('id')->on('peppol_integrations')->onDelete('cascade');
            $table->index(['integration_id', 'config_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peppol_integration_config');
    }
};
