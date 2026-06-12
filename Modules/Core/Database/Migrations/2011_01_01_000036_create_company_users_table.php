<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('company_user', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index('company_users_company_id_foreign');
            $table->unsignedBigInteger('user_id')->index('company_users_user_id_foreign');

            $table->foreign('company_id', 'company_users_company_id_foreign')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id', 'company_users_user_id_foreign')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_user');
    }
};
