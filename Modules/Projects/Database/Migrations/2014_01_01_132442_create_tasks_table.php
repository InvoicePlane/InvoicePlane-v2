<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table): void {
            $table->increments('task_id');
            $table->unsignedInteger('project_id');
            $table->string('task_name');
            $table->longText('task_description');
            $table->decimal('task_price', 20)->nullable();
            $table->date('task_finish_date');
            $table->unsignedTinyInteger('task_status');
            $table->unsignedInteger('tax_rate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
