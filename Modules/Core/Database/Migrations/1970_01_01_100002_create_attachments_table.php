<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('attachable_id')->unsigned();
            $table->string('attachable_type');
            $table->unsignedBiginteger('client_visibility');
            $table->string('filename');
            $table->string('mimetype');
            $table->unsignedBiginteger('size');
            $table->string('url_key');

            $table->foreign('user_id', 'fk_attachments_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
