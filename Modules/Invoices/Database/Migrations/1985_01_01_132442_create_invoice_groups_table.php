<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('invoice_groups', function (Blueprint $table): void {
            $table->increments('invoice_group_id');
            $table->string('invoice_group_name');
            $table->string('invoice_group_identifier_format');
            $table->integer('invoice_group_next_id')->index('invoice_group_next_id');
            $table->integer('invoice_group_left_pad')->default(0)->index('invoice_group_left_pad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_groups');
    }
};
