<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('import_details', function (Blueprint $table): void {
            $table->increments('import_detail_id');
            $table->unsignedInteger('import_id');
            $table->string('import_lang_key', 35);
            $table->string('import_table_name', 35);
            $table->integer('import_record_id');

            $table->index(['import_id', 'import_record_id'], 'import_details_import_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_details');
    }
};
