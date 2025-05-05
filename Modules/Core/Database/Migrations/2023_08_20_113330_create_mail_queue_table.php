<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('mail_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBiginteger('mailable_id');
            $table->string('mailable_type');
            $table->string('from');
            $table->string('to');
            $table->string('cc');
            $table->string('bcc');
            $table->string('subject');
            $table->longText('body');
            $table->boolean('attach_pdf');
            $table->boolean('is_sent');
            $table->text('error')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mail_queue');
    }
};
