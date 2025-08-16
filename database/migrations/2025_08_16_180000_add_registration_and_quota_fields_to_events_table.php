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
            // Registration settings
            $table->timestamp('registration_opens_at')->nullable()->after('end_date');
            $table->timestamp('registration_closes_at')->nullable()->after('registration_opens_at');

            // Quota settings
            $table->integer('max_participants')->nullable()->after('registration_closes_at');
            $table->integer('current_participants')->default(0)->after('max_participants');

            // Event status
            $table->enum('status', ['draft', 'published', 'registration_open', 'registration_closed', 'event_closed'])->default('draft')->after('current_participants');

            // Event closure settings
            $table->timestamp('event_closed_at')->nullable()->after('status');
            $table->boolean('allow_gallery_after_close')->default(false)->after('event_closed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'registration_opens_at',
                'registration_closes_at',
                'max_participants',
                'current_participants',
                'status',
                'event_closed_at',
                'allow_gallery_after_close'
            ]);
        });
    }
};
