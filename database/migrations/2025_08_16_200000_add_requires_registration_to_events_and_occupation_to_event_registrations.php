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
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('requires_registration')->default(true)->after('allow_gallery_after_close');
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->string('occupation', 255)->nullable()->after('referral_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('requires_registration');
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn('occupation');
        });
    }
};
