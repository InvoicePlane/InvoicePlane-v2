<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('relations', function (Blueprint $table): void {
            $table->text('email_cc')->nullable()->after('language');
        });
    }

    public function down(): void
    {
        Schema::table('relations', function (Blueprint $table): void {
            $table->dropColumn('email_cc');
        });
    }
};
