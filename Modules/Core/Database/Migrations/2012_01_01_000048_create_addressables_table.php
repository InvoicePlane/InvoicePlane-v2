<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('addressables', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('address_id')->index();
            $table->morphs('addressable'); // addressable_type + addressable_id
            $table->string('type'); // billing, shipping, office, etc. (Enum)
            $table->boolean('is_primary')->default(false);

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addressables');
    }
};
