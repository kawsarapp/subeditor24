<?php

namespace App\Modules\AudioNarration\Services;

use App\Models\NewsItem;
use App\Models\User;
use App\Models\UserSetting;
use App\Modules\AudioNarration\Contracts\TtsEngineInterface;
use App\Modules\AudioNarration\Engines\EdgeTtsEngine;
use App\Modules\AudioNarration\Engines\ElevenLabsEngine;
use App\Modules\AudioNarration\Engines\GoogleTtsEngine;
use App\Modules\AudioNarration\Engines\OpenAiTtsEngine;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AudioNarrationService
{
    protected array $engines = [];

    public function __construct()
    {
        $this->engines = [
            'edgetts'    => new EdgeTtsEngine(),
            'openai'     => new OpenAiTtsEngine(),
            'google'     => new GoogleTtsEngine(),
            'elevenlabs' => new ElevenLabsEngine(),
        ];
    }

    /**
     * Get specific TTS engine by name
     */
    public function getEngine(string $provider): TtsEngineInterface
    {
        return $this->engines[$provider] ?? $this->engines['edgetts'];
    }

    /**
     * Generate Audio Narration for a NewsItem
     *
     * @param NewsItem $news
     * @param User|null $user
     * @param array $overrides [provider, voice, gender, speed, force_regenerate]
     * @return array
     */
    public function generateForNews(NewsItem $news, ?User $user = null, array $overrides = []): array
    {
        $adminUser = $this->resolveEffectiveAdmin($user ?? ($news->staff_id ? User::find($news->staff_id) : null));
        $settings = $adminUser ? UserSetting::where('user_id', $adminUser->id)->first() : null;

        // Check if TTS is globally enabled or forced
        $force = !empty($overrides['force_regenerate']);
        if (!$force && $settings && !$settings->tts_enabled) {
            return [
                'success' => false,
                'error'   => 'TTS is disabled in user settings.',
            ];
        }

        // Prepare Broadcast News Script
        $script = $this->prepareNewsScript($news);
        if (empty(trim($script))) {
            return [
                'success' => false,
                'error'   => 'News content is empty or invalid for speech synthesis.',
            ];
        }

        // Resolve Provider & Voice Settings
        $provider = $overrides['provider'] ?? ($settings->tts_provider ?? 'edgetts');
        $gender   = $overrides['gender']   ?? ($settings->tts_selected_gender ?? 'male');
        $speed    = floatval($overrides['speed'] ?? ($settings->tts_speed ?? 1.00));
        
        $customVoice = $overrides['voice'] ?? null;
        if (!$customVoice && $settings) {
            $customVoice = ($gender === 'female') ? $settings->tts_voice_female : $settings->tts_voice_male;
        }

        $apiKey = $this->resolveApiKey($provider, $settings);

        $engine = $this->getEngine($provider);
        $options = [
            'gender'           => $gender,
            'voice'            => $customVoice,
            'speed'            => $speed,
            'api_key'          => $apiKey,
            'file_name'        => 'news_' . $news->id . '_' . Str::slug($provider) . '_' . time() . '.mp3',
            'force_regenerate' => $force,
        ];

        Log::info("🎙️ Generating AI Audio for News ID #{$news->id} using {$provider} (Gender: {$gender}, Speed: {$speed})");

        $news->update(['audio_status' => 'processing']);

        $result = $engine->synthesize($script, $options);

        // Fallback Mechanism: If external paid provider fails, try EdgeTTS
        if (!$result['success'] && $provider !== 'edgetts') {
            Log::warning("⚠️ Provider '{$provider}' failed ({$result['error']}). Executing automatic fallback to EdgeTTS...");
            $fallbackEngine = $this->getEngine('edgetts');
            $fallbackOptions = array_merge($options, [
                'voice'     => null, // use default EdgeTTS voice
                'file_name' => 'news_' . $news->id . '_edgetts_fallback_' . time() . '.mp3',
            ]);
            $result = $fallbackEngine->synthesize($script, $fallbackOptions);
        }

        if ($result['success']) {
            $news->update([
                'audio_url'      => $result['audio_url'],
                'audio_path'     => $result['audio_path'],
                'audio_provider' => $result['audio_provider'] ?? $provider,
                'audio_voice'    => $result['audio_voice'] ?? ($customVoice ?? $gender),
                'audio_status'   => 'completed',
            ]);

            Log::info("✅ AI Audio generated successfully for News ID #{$news->id}: {$result['audio_url']}");
            return $result;
        } else {
            $news->update(['audio_status' => 'failed']);
            Log::error("❌ AI Audio generation failed for News ID #{$news->id}: " . ($result['error'] ?? 'Unknown error'));
            return $result;
        }
    }

    /**
     * Convert news title and HTML content into natural, clean broadcast speech
     */
    public function prepareNewsScript(NewsItem $news): string
    {
        $title = trim(strip_tags((string)($news->title ?: $news->ai_title)));
        $rawContent = (string)($news->content ?: $news->ai_content ?: $news->raw_content);

        // 1. Clean HTML tags, styles, scripts and embeds
        $cleanContent = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $rawContent);
        $cleanContent = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $cleanContent);
        $cleanContent = preg_replace('/<figure\b[^>]*>(.*?)<\/figure>/is', '', $cleanContent);
        $cleanContent = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $cleanContent);
        $cleanContent = strip_tags($cleanContent);

        // 2. Remove URLs, shortcodes, and markdown links
        $cleanContent = preg_replace('/\[audio_player\]|\[audio\]|\{\{audio_player\}\}|<!--audio_player-->/i', '', $cleanContent);
        $cleanContent = preg_replace('/https?:\/\/\S+/i', '', $cleanContent);
        $cleanContent = preg_replace('/\[(.*?)\]\((.*?)\)/', '$1', $cleanContent);

        // 3. Normalization: Convert English digits (0-9) to Bengali digits (০-৯) for fluent pronunciation
        $enDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $cleanContent = str_replace($enDigits, $bnDigits, $cleanContent);
        $title = str_replace($enDigits, $bnDigits, $title);

        // 4. Replace common symbols and abbreviations with natural spoken Bengali
        $replacements = [
            '৳'       => ' টাকা ',
            '$'       => ' ডলার ',
            '%'       => ' শতাংশ ',
            ' & '     => ' এবং ',
            ' / '     => ' অথবা ',
            'ড.'      => 'ডক্টর ',
            'মো.'     => 'মোহাম্মদ ',
            'ইং'      => 'ইংরেজি ',
            'কি.মি.'  => 'কিলোমিটার ',
            'কিমি'    => 'কিলোমিটার ',
            'কেজি'    => 'কেজি ',
            'ইসি'     => 'ই সি ',
            'বিজিবি'  => 'বি জি বি ',
            'র‌্যাব'   => 'র‌্যাব ',
            'ডিবি'    => 'ডি বি ',
            'সিআইডি'  => 'সি আই ডি ',
            'ভ্যাট'   => 'ভ্যাট ',
        ];
        $cleanContent = str_replace(array_keys($replacements), array_values($replacements), $cleanContent);

        // 5. Clean up whitespaces and linebreaks into clean pauses
        $cleanContent = preg_replace('/[\r\n\t]+/', ' ', $cleanContent);
        $cleanContent = preg_replace('/\s+/', ' ', $cleanContent);
        $cleanContent = trim($cleanContent);

        // 6. Format broadcast script
        if (!empty($title)) {
            $titlePunct = (str_ends_with($title, '।') || str_ends_with($title, '?') || str_ends_with($title, '!')) ? '' : '।';
            return "{$title}{$titlePunct} বিস্তারিত সংবাদ: {$cleanContent}";
        }

        return $cleanContent;
    }

    /**
     * Get all available voices across all providers
     */
    public function getAllVoices(): array
    {
        $all = [];
        foreach ($this->engines as $key => $engine) {
            $all[$key] = [
                'provider' => $key,
                'voices'   => $engine->getAvailableVoices(),
            ];
        }
        return $all;
    }

    /**
     * Resolve effective Admin user for permissions and settings
     */
    protected function resolveEffectiveAdmin(?User $user): ?User
    {
        if (!$user) {
            return User::where('role', 'super_admin')->first();
        }
        if ($user->role === 'super_admin') {
            return $user;
        }
        if ($user->admin_id) {
            return User::find($user->admin_id) ?: $user;
        }
        return User::where('role', 'super_admin')->first() ?: $user;
    }

    /**
     * Resolve API key for provider from settings, fallback to Super Admin or env
     */
    protected function resolveApiKey(string $provider, ?UserSetting $settings): ?string
    {
        $userId = $settings?->user_id;

        return match ($provider) {
            'openai'     => UserSetting::getSettingWithFallback($userId, 'tts_openai_key')
                            ?: UserSetting::getSettingWithFallback($userId, 'openai_api_key')
                            ?: config('services.openai.api_key'),
            'elevenlabs' => UserSetting::getSettingWithFallback($userId, 'tts_elevenlabs_key')
                            ?: config('services.elevenlabs.api_key'),
            'google'     => UserSetting::getSettingWithFallback($userId, 'tts_google_key')
                            ?: config('services.google.tts_api_key'),
            default      => null,
        };
    }
}
