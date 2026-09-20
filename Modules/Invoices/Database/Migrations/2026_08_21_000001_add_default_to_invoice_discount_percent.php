<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // Matches its sibling invoice_discount_amount, which already has
        // ->default(0) — without this, the NOT NULL column relied entirely
        // on InvoiceService's `?? 0` fallback and a form field that claimed
        // ->nullable() despite the DB requiring a value.
        Schema::table('invoices', function (Blueprint $table): void {
            $table->decimal('invoice_discount_percent', 20)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->decimal('invoice_discount_percent', 20)->default(null)->change();
        });
    }
};
