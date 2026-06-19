<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table): void {
            $table->increments('payment_method_id');
            $table->string('payment_method_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
