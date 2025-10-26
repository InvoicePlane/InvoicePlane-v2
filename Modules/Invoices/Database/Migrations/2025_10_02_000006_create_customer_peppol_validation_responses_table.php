<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the customer_peppol_validation_responses table.
     *
     * The table contains an auto-incrementing primary key `id`, `validation_history_id` (unsigned big integer)
     * referencing `customer_peppol_validation_history.id` with cascade on delete (constraint name `fk_peppol_validation_responses`),
     * `response_key` (string, max 100), and `response_value` (text). An index on `validation_history_id` and `response_key`
     * is created named `idx_validation_responses`.
     */
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

    /**
     * Remove the customer_peppol_validation_responses table from the database.
     *
     * Drops the table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_peppol_validation_responses');
    }
};