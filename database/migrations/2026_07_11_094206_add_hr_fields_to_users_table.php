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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('working_location');
            $table->string('nid')->nullable()->after('phone');
            $table->string('emergency_contact')->nullable()->after('nid');
            $table->string('blood_group')->nullable()->after('emergency_contact');
            $table->text('present_address')->nullable()->after('blood_group');
            $table->text('permanent_address')->nullable()->after('present_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'nid', 'emergency_contact', 'blood_group', 'present_address', 'permanent_address']);
        });
    }
};
