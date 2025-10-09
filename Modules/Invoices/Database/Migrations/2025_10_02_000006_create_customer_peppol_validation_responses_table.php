<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_peppol_validation_responses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('validation_history_id');
            $table->string('response_key', 100);
            $table->text('response_value');
            
            $table->foreign('validation_history_id', 'fk_peppol_validation_responses')
                ->references('id')->on('customer_peppol_validation_history')->onDelete('cascade');
            $table->index(['validation_history_id', 'response_key'], 'idx_validation_responses');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_peppol_validation_responses');
    }
};
