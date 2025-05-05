<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 50)->index('setting_key');
            $table->longText('setting_value');
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
