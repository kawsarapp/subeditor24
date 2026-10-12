<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allPermissions = Role::getAllPermissionSlugs();

        $roles = [
            [
                'name'                => 'super_admin',
                'display_name'        => 'সুপার অ্যাডমিন (Super Admin)',
                'description'         => 'পূর্ণ সিস্টেম অ্যাক্সেস এবং কন্ট্রোল',
                'is_system'           => true,
                'default_permissions' => $allPermissions,
            ],
            [
                'name'                => 'admin',
                'display_name'        => 'অ্যাডমিন / ক্লায়েন্ট (Admin)',
                'description'         => 'নিউজ ব্যবস্থাপনা, সেটিংস, রিপোর্টার ও স্টাফ ম্যানেজমেন্ট',
                'is_system'           => true,
                'default_permissions' => $allPermissions,
            ],
            [
                'name'                => 'editor',
                'display_name'        => 'নিউজ এডিটর (Editor)',
                'description'         => 'নিউজ সম্পাদন, সরাসরি প্রকাশ, এআই রিরাইট এবং রিপোর্টার নিয়ন্ত্রণ',
                'is_system'           => true,
                'default_permissions' => [
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
                ],
            ],
            [
                'name'                => 'sub_editor',
                'display_name'        => 'সহকারী সম্পাদক (Sub-Editor)',
                'description'         => 'সংবাদ তৈরি, এআই রিরাইট, ফটো কার্ড ও স্টুডিও ডিজাইন',
                'is_system'           => true,
                'default_permissions' => [
                    'can_latest_news',
                    'can_direct_publish',
                    'can_ai',
                    'can_view_published',
                    'can_studio',
                    'can_custom_photo_card',
                ],
            ],
            [
                'name'                => 'staff',
                'display_name'        => 'স্টাফ / সহকর্মী (Staff)',
                'description'         => 'কাস্টমাইজড পারমিশন সম্বলিত সাধারণ স্টাফ অ্যাকাউন্ট',
                'is_system'           => true,
                'default_permissions' => [
                    'can_latest_news',
                    'can_direct_publish',
                    'can_studio',
                    'can_custom_photo_card',
                ],
            ],
            [
                'name'                => 'reporter',
                'display_name'        => 'স্টাফ রিপোর্টার (Reporter)',
                'description'         => 'সংবাদ তৈরি এবং সরাসরি রিপোর্টিং',
                'is_system'           => true,
                'default_permissions' => [
                    'can_latest_news',
                    'reporter_direct',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }
}
