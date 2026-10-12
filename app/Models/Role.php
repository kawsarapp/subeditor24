<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_system',
        'default_permissions',
    ];

    protected $casts = [
        'default_permissions' => 'array',
        'is_system'           => 'boolean',
    ];

    /**
     * Relationship: Users with this role
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role', 'name');
    }

    /**
     * Alias permissions to default_permissions for backward compatibility
     */
    public function getPermissionsAttribute()
    {
        return $this->default_permissions;
    }

    public function setPermissionsAttribute($value)
    {
        $this->default_permissions = $value;
    }

    /**
     * Returns list of all 34 system permissions grouped by category:
     * (Editorial, Creative Studio, AI & Automation, SEO Intelligence, Team & Reporters, Settings).
     *
     * @return array<string, array<string, string>>
     */
    public static function getAllPermissionsDictionary(): array
    {
        return [
            'Editorial' => [
                'can_latest_news'    => '📰 Latest News Feed Access',
                'can_scrape'         => '🌐 News Scraper Access',
                'can_central_feed'   => '⚡ Central Live Feed & Wire Pool',
                'can_direct_publish' => '📝 Direct Create (News Feed)',
                'can_view_published' => '📋 View Published News Log',
                'can_fact_check'     => '🔍 Fact Check & Plagiarism Finder',
            ],
            'Creative Studio' => [
                'can_studio'            => '🎨 Studio Design Access',
                'can_custom_photo_card' => '🪄 Photo Card Studio & Quick Cards',
                'manage_templates'      => '🎴 Card Frame Templates',
            ],
            'AI & Automation' => [
                'can_ai'               => '🤖 AI Content Rewriter & Drafts',
                'can_youtube_automate' => '🎬 YouTube AI Automation & Video SEO',
                'can_viral_predictor'  => '🔥 AI Viral & Trending Predictor',
                'can_auto_post'        => '🚀 Automation & Auto Post',
            ],
            'SEO Intelligence' => [
                'can_seo_intelligence' => '🔍 SEO & Website Intelligence (Full Suite)',
                'can_seo_audit'        => '🔍 SEO: Technical Audit Access',
                'can_seo_gsc'          => '🏆 SEO: Google Top 1-3 & Quick Win Keywords',
                'can_seo_ga4'          => '📊 SEO: GA4 Traffic Trends & Decay Alert',
                'can_seo_cwv'          => '⚡ SEO: Core Web Vitals Performance',
                'can_seo_links'        => '🔗 SEO: Orphan News & Broken Links Detector',
                'can_seo_ai'           => '🤖 SEO: AI Meta Auto-Fixer & Generator',
            ],
            'Team & Reporters' => [
                'can_manage_staff' => '👥 Client can create Sub-Users/Staff',
                'manage_reporters' => '👥 Reporter Management & Submissions',
                'reporter_direct'  => '✍️ Reporter Direct Publish',
                'can_analytics'    => '📊 View Analytics & Performance',
            ],
            'Settings' => [
                'can_settings'                 => '⚙️ Settings Page Access',
                'can_settings_branding'        => '🎨 Branding Settings',
                'can_settings_proxy'           => '🌐 Proxy & Scraper Settings',
                'can_settings_target_language' => '🌍 Target Language Settings',
                'can_settings_ai'              => '🤖 AI API Settings',
                'can_settings_ai_prompt'       => '✍️ Custom AI Rewrite Prompt Settings',
                'can_settings_tts'             => '🎙️ Voice Narration (TTS / Audio News) Settings',
                'can_settings_wp_laravel'      => '🔗 WordPress & Laravel API',
                'can_settings_social'          => '📱 Social Media (FB, X, Telegram)',
                'can_settings_category'        => '📂 Category Mapping',
            ],
        ];
    }

    /**
     * Get a flat list of all 34 permission slugs
     *
     * @return array<string>
     */
    public static function getAllPermissionSlugs(): array
    {
        $slugs = [];
        foreach (self::getAllPermissionsDictionary() as $group) {
            foreach ($group as $slug => $label) {
                $slugs[] = $slug;
            }
        }
        return $slugs;
    }

    /**
     * Backward-compatible alias for getPermissionsByCategory
     */
    public static function getPermissionsByCategory(): array
    {
        $meta = [
            'Editorial' => ['name' => 'সম্পাদকীয় ও নিউজ ফিড', 'icon' => 'fa-solid fa-newspaper', 'color' => 'indigo'],
            'Creative Studio' => ['name' => 'স্টুডিও ও মিডিয়া ডিজাইন', 'icon' => 'fa-solid fa-palette', 'color' => 'purple'],
            'AI & Automation' => ['name' => 'এআই ও ট্রেন্ডস ইন্টেলিজেন্স', 'icon' => 'fa-solid fa-wand-magic-sparkles', 'color' => 'amber'],
            'SEO Intelligence' => ['name' => 'এসইও ও গ্রোথ অ্যানালিটিক্স', 'icon' => 'fa-solid fa-chart-line', 'color' => 'emerald'],
            'Team & Reporters' => ['name' => 'টিম ও রিপোর্টার্স', 'icon' => 'fa-solid fa-users', 'color' => 'blue'],
            'Settings' => ['name' => 'সিস্টেম সেটিংস', 'icon' => 'fa-solid fa-gear', 'color' => 'slate'],
        ];

        $result = [];
        foreach (self::getAllPermissionsDictionary() as $cat => $perms) {
            $result[$cat] = array_merge($meta[$cat] ?? ['name' => $cat, 'icon' => 'fa-solid fa-shield', 'color' => 'indigo'], [
                'permissions' => $perms,
            ]);
        }
        return $result;
    }

    /**
     * Backward-compatible alias for getAllPermissions
     */
    public static function getAllPermissions(): array
    {
        $all = [];
        foreach (self::getAllPermissionsDictionary() as $group) {
            foreach ($group as $slug => $label) {
                $all[$slug] = $label;
            }
        }
        return $all;
    }

    /**
     * Get predefined default permissions for a role name
     *
     * @param string $roleName
     * @return array<string>
     */
    public static function getDefaultPermissionsForRole(string $roleName): array
    {
        return match ($roleName) {
            'super_admin', 'admin' => self::getAllPermissionSlugs(),
            'editor' => [
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
            'sub_editor' => [
                'can_latest_news',
                'can_direct_publish',
                'can_ai',
                'can_view_published',
                'can_studio',
                'can_custom_photo_card',
            ],
            'staff' => [
                'can_latest_news',
                'can_direct_publish',
                'can_studio',
                'can_custom_photo_card',
            ],
            'reporter' => [
                'can_latest_news',
                'reporter_direct',
            ],
            default => [],
        };
    }

    /**
     * Backward-compatible alias
     */
    public static function getDefaultPermissionsFor(string $role): array
    {
        try {
            if (Schema::hasTable('roles')) {
                $record = static::where('name', $role)->first();
                if ($record && is_array($record->default_permissions)) {
                    return $record->default_permissions;
                }
            }
        } catch (\Throwable $e) {
            // DB table might not exist yet
        }

        $cached = Cache::get('role_permissions_' . $role);
        if (is_array($cached)) {
            return $cached;
        }

        return self::getDefaultPermissionsForRole($role);
    }

    /**
     * Get SaaS assignable roles list
     */
    public static function getAssignableRoles(): array
    {
        return [
            'editor' => [
                'name' => 'editor',
                'display_name' => '👑 নিউজ এডিটর (News Editor)',
                'badge_color' => 'indigo',
                'description' => 'সম্পাদকীয় টিম প্রধান - সব ধরণের নিউজ তৈরি, এডিট ও প্রকাশনার পূর্ণ ক্ষমতা',
            ],
            'sub_editor' => [
                'name' => 'sub_editor',
                'display_name' => '📝 সহ-সম্পাদক (Sub-Editor)',
                'badge_color' => 'blue',
                'description' => 'সংবাদ প্রক্রিয়াকরণ, ড্রাফট তৈরি, এআই রিরাইট ও ফটো কার্ড ডিজাইন',
            ],
            'staff' => [
                'name' => 'staff',
                'display_name' => '👥 স্টাফ / অপারেটর (Newsroom Staff)',
                'badge_color' => 'emerald',
                'description' => 'নিউজ রুম অপারেটর - নিউজ ড্রাফট ও প্রাথমিক কাজ',
            ],
            'reporter' => [
                'name' => 'reporter',
                'display_name' => '🎙️ মাঠ পর্যায়ের রিপোর্টার (Reporter)',
                'badge_color' => 'amber',
                'description' => 'মাঠ পর্যায় থেকে সংবাদ পাঠানো ও নিজস্ব নিউজ দেখার অনুমতি',
            ],
        ];
    }

    /**
     * Update default permissions for a role (Super Admin only).
     */
    public static function updateDefaultPermissions(string $role, array $permissions): void
    {
        $permissions = array_values(array_unique($permissions));

        try {
            if (Schema::hasTable('roles')) {
                $meta = static::getAssignableRoles()[$role] ?? null;
                static::updateOrCreate(
                    ['name' => $role],
                    [
                        'display_name' => $meta['display_name'] ?? ucfirst($role),
                        'description' => $meta['description'] ?? null,
                        'default_permissions' => $permissions,
                        'is_system' => true,
                    ]
                );
            }
        } catch (\Throwable $e) {
            // Log or fallback
        }

        Cache::forever('role_permissions_' . $role, $permissions);
    }

    /**
     * Get all assignable roles along with resolved permissions
     */
    public static function getRolesWithPermissions(): array
    {
        $roles = static::getAssignableRoles();
        foreach ($roles as $key => &$r) {
            $r['resolved_permissions'] = static::getDefaultPermissionsFor($key);
        }
        return $roles;
    }
}
