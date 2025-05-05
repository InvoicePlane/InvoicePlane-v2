<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('type'); // PHP Enum (billing, shipping, office)
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('number')->nullable();
            $table->string('postal_code');
            $table->string('city');
            $table->string('state_or_province')->nullable();
            $table->string('country');

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
