<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('project_number')->nullable();
            $table->string('project_status');
            $table->string('project_name')->nullable()->comment('nullable for legacy reason but does not make sense');
            $table->date('start_at')->nullable();
            $table->date('end_at')->nullable();

            $table->text('description')->nullable();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('relations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
