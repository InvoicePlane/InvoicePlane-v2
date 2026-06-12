<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('uploads', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id')->nullable()->index('uploads_user_id_foreign');
            $table->morphs('uploadable');
            $table->string('upload_original_name', 100);
            $table->string('upload_stored_name');
            $table->string('upload_mime_type', 30);
            $table->string('upload_url_key', 20)->unique('uploads_url_key_unique');
            $table->string('upload_disk')->default('public');
            $table->string('file_description');

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id', 'uploads_user_id_foreign')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
