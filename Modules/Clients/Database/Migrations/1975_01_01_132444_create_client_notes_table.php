<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('client_notes', function (Blueprint $table): void {
            $table->integer('client_note_id', true);
            $table->integer('client_id');
            $table->date('client_note_date');
            $table->longText('client_note');

            $table->index(['client_id', 'client_note_date'], 'client_notes_client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_notes');
    }
};
