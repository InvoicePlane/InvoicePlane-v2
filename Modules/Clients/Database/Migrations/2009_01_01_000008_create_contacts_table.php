<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('relation_id');
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->boolean('default_to')->nullable();
            $table->boolean('default_cc')->nullable();
            $table->boolean('default_bcc')->nullable();
            $table->string('gender', 10)->nullable();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('relation_id', 'contacts_relation_id_foreign')
                ->references('id')
                ->on('relations')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::table('relations', function (Blueprint $table): void {
            $table->foreign('primary_contact_id', 'relations_contact_id_foreign')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
