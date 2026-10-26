<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('tax_rates', function (Blueprint $table): void {
            $table->unique(['company_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('tax_rates', function (Blueprint $table): void {
            $table->dropUnique(['company_id', 'code']);
        });
    }
};
