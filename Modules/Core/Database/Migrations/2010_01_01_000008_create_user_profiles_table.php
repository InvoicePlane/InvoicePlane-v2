<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('user_phone')->nullable();
            $table->string('user_mobile')->nullable();
            $table->string('user_language')->default('system');
            $table->string('user_web')->nullable();
            $table->string('user_vat_id')->nullable();
            $table->string('user_tax_code')->nullable();
            $table->string('user_iban', 34)->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
