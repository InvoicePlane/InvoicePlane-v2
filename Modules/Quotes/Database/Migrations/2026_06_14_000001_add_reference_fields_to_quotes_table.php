<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table): void {
            $table->string('client_reference')->nullable()->after('quote_number');
            $table->string('work_order')->nullable()->after('client_reference');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table): void {
            $table->dropColumn(['client_reference', 'work_order']);
        });
    }
};
