<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Create the peppol_transmission_responses database table.
     *
     * The table contains an auto-incrementing primary key `id`, an unsigned big integer
     * `transmission_id` referencing `peppol_transmissions.id` with cascade on delete,
     * a `response_key` string (maximum 100 characters), and a `response_value` text column.
     * Also adds a composite index on (`transmission_id`, `response_key`).
     */
    public function up(): void
    {
        Schema::create('peppol_transmission_responses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('transmission_id');
            $table->string('response_key', 100);
            $table->text('response_value');

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('transmission_id')->references('id')->on('peppol_transmissions')->onDelete('cascade');
            $table->index(['company_id', 'transmission_id']);
            $table->index(['transmission_id', 'response_key']);
        });
    }

    /**
     * Reverts the migration by dropping the `peppol_transmission_responses` table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('peppol_transmission_responses');
    }
};
