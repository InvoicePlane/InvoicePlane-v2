<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // Rename document_group_id to numbering_id in invoices table
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropForeign('invoices_document_group_id_foreign');
            $table->dropIndex('invoices_document_group_id_foreign');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->renameColumn('document_group_id', 'numbering_id');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->index('numbering_id', 'invoices_numbering_id_index');
            $table->foreign('numbering_id', 'invoices_numbering_id_foreign')
                  ->references('numbering_id')->on('numbering')->onDelete('restrict');
        });

        // Rename document_group_id to numbering_id in quotes table
        Schema::table('quotes', function (Blueprint $table): void {
            $table->dropForeign('quotes_document_group_id_foreign');
            $table->dropIndex('quotes_document_group_id_foreign');
        });

        Schema::table('quotes', function (Blueprint $table): void {
            $table->renameColumn('document_group_id', 'numbering_id');
        });

        Schema::table('quotes', function (Blueprint $table): void {
            $table->index('numbering_id', 'quotes_numbering_id_index');
            $table->foreign('numbering_id', 'quotes_numbering_id_foreign')
                  ->references('numbering_id')->on('numbering')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        // Revert invoices table
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropForeign('invoices_numbering_id_foreign');
            $table->dropIndex('invoices_numbering_id_index');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->renameColumn('numbering_id', 'document_group_id');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->index('document_group_id', 'invoices_document_group_id_foreign');
            $table->foreign('document_group_id', 'invoices_document_group_id_foreign')
                  ->references('id')->on('document_groups')->onDelete('restrict');
        });

        // Revert quotes table
        Schema::table('quotes', function (Blueprint $table): void {
            $table->dropForeign('quotes_numbering_id_foreign');
            $table->dropIndex('quotes_numbering_id_index');
        });

        Schema::table('quotes', function (Blueprint $table): void {
            $table->renameColumn('numbering_id', 'document_group_id');
        });

        Schema::table('quotes', function (Blueprint $table): void {
            $table->index('document_group_id', 'quotes_document_group_id_foreign');
            $table->foreign('document_group_id', 'quotes_document_group_id_foreign')
                  ->references('id')->on('document_groups')->onDelete('restrict');
        });
    }
};
