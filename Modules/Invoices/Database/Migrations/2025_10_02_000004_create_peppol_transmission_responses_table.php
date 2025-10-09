<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peppol_transmission_responses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('transmission_id');
            $table->string('response_key', 100);
            $table->text('response_value');
            
            $table->foreign('transmission_id')->references('id')->on('peppol_transmissions')->onDelete('cascade');
            $table->index(['transmission_id', 'response_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peppol_transmission_responses');
    }
};
