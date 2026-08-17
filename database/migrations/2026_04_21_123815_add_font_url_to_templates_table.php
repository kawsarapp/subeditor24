<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            // Custom font file URL (CDN বা server hosted .ttf/.woff/.woff2)
            // খালি থাকলে fontFamily field এর value use হবে (built-in)
            $table->string('font_url')->nullable()->after('layout_data');
        });
    }

    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn('font_url');
        });
    }
};
