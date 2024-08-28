<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('invoice_custom', function (Blueprint $table): void {
            $table->integer('invoice_custom_id', true);
            $table->integer('invoice_id');
            $table->integer('invoice_custom_fieldid');
            $table->string('invoice_custom_fieldvalue')->nullable();

            $table->unique(['invoice_id', 'invoice_custom_fieldid'], 'inv_custom_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_custom');
    }
};
