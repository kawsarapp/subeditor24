<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(true);
            $table->json('default_permissions')->nullable();
            $table->timestamps();
        });

        // 34 System Permissions
        $allPermissions = [
            'can_latest_news',
            'can_scrape',
            'can_central_feed',
            'can_direct_publish',
            'can_view_published',
            'can_fact_check',
            'can_studio',
            'can_custom_photo_card',
            'manage_templates',
            'can_ai',
            'can_youtube_automate',
            'can_viral_predictor',
            'can_auto_post',
            'can_seo_intelligence',
            'can_seo_audit',
            'can_seo_gsc',
            'can_seo_ga4',
            'can_seo_cwv',
            'can_seo_links',
            'can_seo_ai',
            'can_manage_staff',
            'manage_reporters',
            'reporter_direct',
            'can_analytics',
            'can_settings',
            'can_settings_branding',
            'can_settings_proxy',
            'can_settings_target_language',
            'can_settings_ai',
            'can_settings_ai_prompt',
            'can_settings_tts',
            'can_settings_wp_laravel',
            'can_settings_social',
            'can_settings_category',
        ];

        $now = now();
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'সুপার অ্যাডমিন (Super Admin)',
                'description' => 'পূর্ণ সিস্টেম অ্যাক্সেস এবং কন্ট্রোল',
                'is_system' => true,
                'default_permissions' => json_encode($allPermissions),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'admin',
                'display_name' => 'অ্যাডমিন / ক্লায়েন্ট (Admin)',
                'description' => 'নিউজ ব্যবস্থাপনা, সেটিংস, রিপোর্টার ও স্টাফ ম্যানেজমেন্ট',
                'is_system' => true,
                'default_permissions' => json_encode($allPermissions),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'editor',
                'display_name' => 'নিউজ এডিটর (Editor)',
                'description' => 'নিউজ সম্পাদন, সরাসরি প্রকাশ, এআই রিরাইট এবং রিপোর্টার নিয়ন্ত্রণ',
                'is_system' => true,
                'default_permissions' => json_encode([
                    'can_latest_news',
                    'can_central_feed',
                    'can_direct_publish',
                    'can_ai',
                    'can_view_published',
                    'can_studio',
                    'can_custom_photo_card',
                    'manage_templates',
                    'can_fact_check',
                    'manage_reporters',
                    'can_analytics',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'sub_editor',
                'display_name' => 'সহকারী সম্পাদক (Sub-Editor)',
                'description' => 'সংবাদ তৈরি, এআই রিরাইট, ফটো কার্ড ও স্টুডিও ডিজাইন',
                'is_system' => true,
                'default_permissions' => json_encode([
                    'can_latest_news',
                    'can_direct_publish',
                    'can_ai',
                    'can_view_published',
                    'can_studio',
                    'can_custom_photo_card',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'staff',
                'display_name' => 'স্টাফ / সহকর্মী (Staff)',
                'description' => 'কাস্টমাইজড পারমিশন সম্বলিত সাধারণ স্টাফ অ্যাকাউন্ট',
                'is_system' => true,
                'default_permissions' => json_encode([
                    'can_latest_news',
                    'can_direct_publish',
                    'can_studio',
                    'can_custom_photo_card',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'reporter',
                'display_name' => 'স্টাফ রিপোর্টার (Reporter)',
                'description' => 'সংবাদ তৈরি এবং সরাসরি রিপোর্টিং',
                'is_system' => true,
                'default_permissions' => json_encode([
                    'can_latest_news',
                    'reporter_direct',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('roles')->insert($roles);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
