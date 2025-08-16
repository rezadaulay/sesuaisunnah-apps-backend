<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add missing fields to ebook_interactions table
        Schema::table('ebook_interactions', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });

        // Add missing fields to audiobook_files table
        Schema::table('audiobook_files', function (Blueprint $table) {
            $table->integer('order_number')->nullable()->after('file_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove fields from ebook_interactions table
        Schema::table('ebook_interactions', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });

        // Remove fields from audiobook_files table
        Schema::table('audiobook_files', function (Blueprint $table) {
            $table->dropColumn('order_number');
        });
    }
};
