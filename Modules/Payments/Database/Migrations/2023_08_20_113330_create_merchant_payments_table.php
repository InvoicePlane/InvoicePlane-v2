<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('merchant_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('driver');
            $table->integer('payment_id');
            $table->string('merchant_key');
            $table->string('merchant_value');

            $table->index('driver');
            $table->index('payment_id');
            $table->index('merchant_key');
        });
    }

    public function down()
    {
        Schema::dropIfExists('merchant_payments');
    }
};
