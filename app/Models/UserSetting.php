<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand_name',
        'logo_url',
        'default_theme_color',
        'wp_url',
        'wp_username',
        'wp_app_password',
        'telegram_channel_id',
        'is_auto_posting',
        'auto_post_interval',
        'last_auto_post_at',
		'allowed_templates', 
		'default_template',
		'scraper_method',
		'scrape_cooldown_minutes',
		'scrape_concurrent_limit',
		'category_mapping',
		'design_preferences',
		'fb_page_id',
		'fb_access_token',
		'telegram_bot_token',
		'telegram_channel_id',
		'post_to_fb',
        'post_to_telegram',
        'twitter_api_key',
        'twitter_api_secret',
        'twitter_access_token',
        'twitter_access_secret',
        'post_to_twitter',
		'laravel_site_url',  
		'laravel_api_token', 
		'post_to_laravel',
		'fb_comment_link',
		'proxy_host',
		'proxy_port',
		'proxy_username',
		'proxy_password',
        'openai_api_key',
        'openai_model',
        'gemini_api_key',
        'gemini_model',
        'deepseek_api_key',
        'deepseek_model',
        'primary_ai',
        'groq_api_key',
        'groq_model',
        'qwen_api_key',
        'qwen_model',
        'huggingface_api_key',
        'huggingface_model',
        'smartproxy_api_token',
        'photoroom_api_key',
        'target_language',
    ];


    protected $casts = [
        'allowed_templates' => 'array',
        'is_auto_posting' => 'boolean',
		'category_mapping' => 'array',
		'design_preferences' => 'array',
		'post_to_fb' => 'boolean',
        'post_to_telegram' => 'boolean',
        'post_to_twitter' => 'boolean',
		'post_to_laravel' => 'boolean',
		'wp_app_password' => 'encrypted',
        'fb_access_token' => 'encrypted',
        'telegram_bot_token' => 'encrypted',
        'twitter_api_key' => 'encrypted',
        'twitter_api_secret' => 'encrypted',
        'twitter_access_token' => 'encrypted',
        'twitter_access_secret' => 'encrypted',
        'laravel_api_token' => 'encrypted',
        'openai_api_key' => 'encrypted',
        'gemini_api_key' => 'encrypted',
        'deepseek_api_key' => 'encrypted',
        'groq_api_key' => 'encrypted',
        'qwen_api_key' => 'encrypted',
        'huggingface_api_key' => 'encrypted',
        'smartproxy_api_token' => 'encrypted',
        'photoroom_api_key' => 'encrypted',
    ];

    public const AVAILABLE_TEMPLATES = [
        'ntv'           => '🟩 NTV News',
        'rtv'           => '🟥 RTV News',
        'dhakapost'     => '🟦 Dhaka Post',
        'todayevents'   => '🟪 Today Events',
		'todayeventsSingle'   => '🟪 Today Events Single',
		'BanglaLiveNews' => 'Bangla Live News',
		'BanglaLiveNews1' => 'Bangla Live News 1',
		'Jaijaidin1' => 'Jaijaidin 1',
		'Jaijaidin2' => 'Jaijaidin 2',
		'Jaijaidin3' => 'Jaijaidin 3',
		'Jaijaidin4' => 'Jaijaidin 4',
		'ShotterKhoje' => 'Shotter Khoje',
		'jonomot' => 'jonomot',
		'Bangladeshmail24' => 'Bangladeshmail24',
		'WatchBangladesh' => 'WatchBangladesh',
		'TodayEventsDualFrame' => 'TodayEventsDualFrame',
		'todayeventsSingle1' => 'todayeventsSingle1',
		'Thenews24Main' => 'Thenews24Main',
		'Thenews24UniversalAds' => 'Thenews24UniversalAds',
		'ITVNews' => 'ITVNews',

		
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getSettingWithFallback($userId, $key)
    {
        // 1. Try finding it for the current user ID
        if ($userId) {
            $setting = self::where('user_id', $userId)->first();
            if ($setting && !empty($setting->$key)) {
                return $setting->$key;
            }
        }
        
        // 2. Try falling back to Super Admin
        $superAdmin = User::where('role', 'super_admin')->first();
        if ($superAdmin) {
            $setting = self::where('user_id', $superAdmin->id)->first();
            if ($setting && !empty($setting->$key)) {
                return $setting->$key;
            }
        }
        
        return null; // Return null so callers can fallback to .env 
    }
}