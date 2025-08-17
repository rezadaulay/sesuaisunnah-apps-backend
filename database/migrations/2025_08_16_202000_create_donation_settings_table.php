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
        Schema::create('donation_settings', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name', 100);
            $table->string('account_number', 50);
            $table->string('account_name', 100);
            $table->string('swift_code', 20)->nullable();
            $table->string('branch_name', 100)->nullable();
            $table->text('donation_note')->nullable();
            $table->text('bank_transfer_note')->nullable();
            $table->decimal('minimum_donation', 10, 2)->default(10000);
            $table->boolean('is_active')->default(true);
            $table->boolean('show_donor_list')->default(false);
            $table->string('contact_person', 100)->nullable();
            $table->string('contact_email', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_settings');
    }
};
