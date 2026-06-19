<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table): void {
            $table->increments('tax_rate_id');
            $table->string('tax_rate_name')->nullable();
            $table->decimal('tax_rate_percent', 5);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
