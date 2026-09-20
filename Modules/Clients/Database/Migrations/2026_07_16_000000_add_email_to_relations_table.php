<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('relations', function (Blueprint $table): void {
            $table->string('email')->nullable()->after('company_name');
        });
    }

    public function down(): void
    {
        Schema::table('relations', function (Blueprint $table): void {
            $table->dropColumn('email');
        });
    }
};
