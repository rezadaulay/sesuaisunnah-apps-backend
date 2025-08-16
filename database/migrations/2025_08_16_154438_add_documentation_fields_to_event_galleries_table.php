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
        Schema::table('event_galleries', function (Blueprint $table) {
            $table->enum('type', ['photo', 'video', 'document'])->default('photo')->after('photo_url');
            $table->text('description')->nullable()->after('type');
            $table->bigInteger('file_size')->nullable()->after('description');
            $table->string('mime_type')->nullable()->after('file_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_galleries', function (Blueprint $table) {
            $table->dropColumn(['type', 'description', 'file_size', 'mime_type']);
        });
    }
};
