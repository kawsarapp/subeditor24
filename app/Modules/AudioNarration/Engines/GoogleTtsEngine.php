<?php

namespace App\Modules\AudioNarration\Engines;

use App\Modules\AudioNarration\Contracts\TtsEngineInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoogleTtsEngine implements TtsEngineInterface
{
    public function getProviderName(): string
    {
        return 'google';
    }

    public function getAvailableVoices(string $lang = 'bn'): array
    {
        return [
            'bn-BD-Wavenet-B' => ['name' => 'Wavenet B (Male, Bangladesh)', 'gender' => 'male', 'lang' => 'bn-BD'],
            'bn-BD-Wavenet-A' => ['name' => 'Wavenet A (Female, Bangladesh)', 'gender' => 'female', 'lang' => 'bn-BD'],
            'bn-IN-Wavenet-B' => ['name' => 'Wavenet B (Male, India)', 'gender' => 'male', 'lang' => 'bn-IN'],
            'bn-IN-Wavenet-A' => ['name' => 'Wavenet A (Female, India)', 'gender' => 'female', 'lang' => 'bn-IN'],
            'bn-IN-Wavenet-C' => ['name' => 'Wavenet C (Male Deep, India)', 'gender' => 'male', 'lang' => 'bn-IN'],
        ];
    }

    public function synthesize(string $text, array $options = []): array
    {
        $apiKey = $options['api_key'] ?? config('services.google.tts_api_key');
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'Google Cloud TTS API key is missing.'];
        }

        $gender = $options['gender'] ?? 'male';
        $voice = $options['voice'] ?? ($gender === 'female' ? 'bn-BD-Wavenet-A' : 'bn-BD-Wavenet-B');
        $langCode = str_starts_with($voice, 'bn-IN') ? 'bn-IN' : 'bn-BD';
        $speakingRate = floatval($options['speed'] ?? 1.00);

        $disk = Storage::disk('public');
        if (!$disk->exists('audio')) {
            $disk->makeDirectory('audio');
        }

        $fileName = $options['file_name'] ?? ('news_voice_google_' . md5($text . $voice . $speakingRate) . '.mp3');
        $relativeStoragePath = 'audio/' . $fileName;
        $absolutePath = storage_path('app/public/' . $relativeStoragePath);

        if ($disk->exists($relativeStoragePath) && filesize($absolutePath) > 1024 && empty($options['force_regenerate'])) {
            return [
                'success'        => true,
                'audio_path'     => $relativeStoragePath,
                'audio_url'      => asset('storage/' . $relativeStoragePath),
                'audio_provider' => $this->getProviderName(),
                'audio_voice'    => $voice,
                'cached'         => true,
                'error'          => null,
            ];
        }

        try {
            $apiUrl = 'https://texttospeech.googleapis.com/v1/text:synthesize?key=' . urlencode($apiKey);

            $response = Http::timeout(60)->post($apiUrl, [
                'input' => ['text' => $text],
                'voice' => [
                    'languageCode' => $langCode,
                    'name'         => $voice,
                    'ssmlGender'   => strtoupper($gender),
                ],
                'audioConfig' => [
                    'audioEncoding' => 'MP3',
                    'speakingRate'  => $speakingRate,
                ],
            ]);

            if (!$response->successful()) {
                $error = $response->json('error.message') ?? ("HTTP " . $response->status() . ": " . $response->body());
                Log::error("❌ GoogleTtsEngine Error: " . $error);
                return ['success' => false, 'error' => $error];
            }

            $audioContent = base64_decode($response->json('audioContent') ?? '');
            if (empty($audioContent)) {
                return ['success' => false, 'error' => 'Google Cloud TTS returned empty audio content.'];
            }

            file_put_contents($absolutePath, $audioContent);

            return [
                'success'        => true,
                'audio_path'     => $relativeStoragePath,
                'audio_url'      => asset('storage/' . $relativeStoragePath),
                'audio_provider' => $this->getProviderName(),
                'audio_voice'    => $voice,
                'cached'         => false,
                'error'          => null,
            ];
        } catch (\Exception $e) {
            Log::error("❌ GoogleTtsEngine Exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
