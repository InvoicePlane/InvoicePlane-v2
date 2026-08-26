<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('quote_signatures', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('quote_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('signer_name');
            $table->string('signature_disk');
            $table->string('signature_path');
            $table->timestamp('signed_at');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('quote_id', 'fk_quote_signatures_quote_id')->references('id')->on('quotes')->onDelete('cascade');
            $table->foreign('user_id', 'fk_quote_signatures_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_signatures');
    }
};
