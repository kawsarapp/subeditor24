<?php

namespace App\Modules\AudioNarration\Engines;

use App\Modules\AudioNarration\Contracts\TtsEngineInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ElevenLabsEngine implements TtsEngineInterface
{
    public function getProviderName(): string
    {
        return 'elevenlabs';
    }

    public function getAvailableVoices(string $lang = 'bn'): array
    {
        return [
            'pNInz6obpgDQGcFmaJgB' => ['name' => 'Adam (Deep News Anchor Male)', 'gender' => 'male', 'lang' => 'multi'],
            '21m00Tcm4TlvDq8ikWAM' => ['name' => 'Rachel (Professional Female Anchor)', 'gender' => 'female', 'lang' => 'multi'],
            'JBFqnCBsd6RMkjVDRZzb' => ['name' => 'George (Authoritative Male Voice)', 'gender' => 'male', 'lang' => 'multi'],
            'EXAVITQu4vr4xnSDxMaL' => ['name' => 'Bella (Dynamic Female Voice)', 'gender' => 'female', 'lang' => 'multi'],
            'onwK4e9ZLuTAKqWW03F9' => ['name' => 'Daniel (British Authoritative Male)', 'gender' => 'male', 'lang' => 'multi'],
        ];
    }

    public function synthesize(string $text, array $options = []): array
    {
        $apiKey = $options['api_key'] ?? config('services.elevenlabs.api_key');
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'ElevenLabs API key is missing.'];
        }

        $gender = $options['gender'] ?? 'male';
        $voiceId = $options['voice'] ?? ($gender === 'female' ? '21m00Tcm4TlvDq8ikWAM' : 'pNInz6obpgDQGcFmaJgB');
        $modelId = $options['model'] ?? 'eleven_multilingual_v2';

        $disk = Storage::disk('public');
        if (!$disk->exists('audio')) {
            $disk->makeDirectory('audio');
        }

        $fileName = $options['file_name'] ?? ('news_voice_eleven_' . md5($text . $voiceId) . '.mp3');
        $relativeStoragePath = 'audio/' . $fileName;
        $absolutePath = storage_path('app/public/' . $relativeStoragePath);

        if ($disk->exists($relativeStoragePath) && filesize($absolutePath) > 1024 && empty($options['force_regenerate'])) {
            return [
                'success'        => true,
                'audio_path'     => $relativeStoragePath,
                'audio_url'      => asset('storage/' . $relativeStoragePath),
                'audio_provider' => $this->getProviderName(),
                'audio_voice'    => $voiceId,
                'cached'         => true,
                'error'          => null,
            ];
        }

        try {
            $apiUrl = "https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}?output_format=mp3_44100_128";

            $response = Http::timeout(90)
                ->withHeaders([
                    'xi-api-key'   => $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept'       => 'audio/mpeg',
                ])
                ->post($apiUrl, [
                    'text'           => $text,
                    'model_id'       => $modelId,
                    'voice_settings' => [
                        'stability'        => 0.50,
                        'similarity_boost' => 0.75,
                        'style'            => 0.00,
                        'use_speaker_boost'=> true,
                    ]
                ]);

            if (!$response->successful()) {
                $error = $response->json('detail.message') ?? ("HTTP " . $response->status() . ": " . $response->body());
                Log::error("❌ ElevenLabsEngine Error: " . $error);
                return ['success' => false, 'error' => $error];
            }

            file_put_contents($absolutePath, $response->body());

            return [
                'success'        => true,
                'audio_path'     => $relativeStoragePath,
                'audio_url'      => asset('storage/' . $relativeStoragePath),
                'audio_provider' => $this->getProviderName(),
                'audio_voice'    => $voiceId,
                'cached'         => false,
                'error'          => null,
            ];
        } catch (\Exception $e) {
            Log::error("❌ ElevenLabsEngine Exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
