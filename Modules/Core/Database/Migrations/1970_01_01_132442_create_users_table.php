<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->increments('user_id');
            $table->unsignedInteger('user_type')->default(0);
            $table->boolean('user_active')->nullable()->default(true);
            $table->dateTime('user_date_created');
            $table->dateTime('user_date_modified');
            $table->string('user_language')->nullable()->default('system');
            $table->string('user_name')->nullable();
            $table->string('user_company')->nullable();
            $table->string('user_address_1')->nullable();
            $table->string('user_address_2')->nullable();
            $table->string('user_city')->nullable();
            $table->string('user_state')->nullable();
            $table->string('user_zip')->nullable();
            $table->string('user_country')->nullable();
            $table->string('user_phone')->nullable();
            $table->string('user_fax')->nullable();
            $table->string('user_mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('user_password', 60);
            $table->string('user_web')->nullable();
            $table->string('user_vat_id')->nullable();
            $table->string('user_tax_code')->nullable();
            $table->string('user_psalt')->nullable();
            $table->boolean('user_all_clients')->default(0);
            $table->string('user_passwordreset_token', 100)->nullable()->default('');
            $table->string('user_subscribernumber', 40)->nullable();
            $table->string('user_iban', 34)->nullable();
            $table->integer('user_gln')->nullable();
            $table->string('user_rcc', 7)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
