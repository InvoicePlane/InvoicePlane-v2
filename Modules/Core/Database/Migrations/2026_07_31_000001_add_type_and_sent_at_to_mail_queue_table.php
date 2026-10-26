<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('mail_queue', function (Blueprint $table): void {
            $table->string('type')->nullable()->after('mailable_type');
            $table->timestamp('sent_at')->nullable()->after('is_sent');
        });
    }

    public function down(): void
    {
        Schema::table('mail_queue', function (Blueprint $table): void {
            $table->dropColumn(['type', 'sent_at']);
        });
    }
};
