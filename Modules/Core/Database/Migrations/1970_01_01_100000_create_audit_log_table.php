<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBiginteger('audit_id');
            $table->string('audit_type');
            $table->string('activity');
            $table->text('info')->nullable();

            $table->index('audit_type');
            $table->index('activity');
            $table->index('audit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
