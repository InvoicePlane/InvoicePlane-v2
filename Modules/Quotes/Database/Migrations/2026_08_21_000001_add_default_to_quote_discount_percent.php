<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // Matches its sibling quote_discount_amount, which already has
        // ->default(0) — without this, the NOT NULL column relied entirely
        // on QuoteService's `?? 0` fallback and a form field that's marked
        // ->dehydrated(false) so it never even reaches that fallback via
        // user input at all.
        Schema::table('quotes', function (Blueprint $table): void {
            $table->decimal('quote_discount_percent', 20)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table): void {
            $table->decimal('quote_discount_percent', 20)->default(null)->change();
        });
    }
};
