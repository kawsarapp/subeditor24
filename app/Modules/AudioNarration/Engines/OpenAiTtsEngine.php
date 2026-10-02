<?php

namespace App\Modules\AudioNarration\Engines;

use App\Modules\AudioNarration\Contracts\TtsEngineInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OpenAiTtsEngine implements TtsEngineInterface
{
    public function getProviderName(): string
    {
        return 'openai';
    }

    public function getAvailableVoices(string $lang = 'bn'): array
    {
        return [
            'onyx'    => ['name' => 'Onyx (Deep Male Newsroom Voice)', 'gender' => 'male', 'lang' => 'multi'],
            'echo'    => ['name' => 'Echo (Balanced Male Voice)', 'gender' => 'male', 'lang' => 'multi'],
            'alloy'   => ['name' => 'Alloy (Neutral Dynamic Voice)', 'gender' => 'neutral', 'lang' => 'multi'],
            'nova'    => ['name' => 'Nova (Crisp Female Broadcast Voice)', 'gender' => 'female', 'lang' => 'multi'],
            'shimmer' => ['name' => 'Shimmer (Warm Female Voice)', 'gender' => 'female', 'lang' => 'multi'],
            'fable'   => ['name' => 'Fable (Expressive British Tone)', 'gender' => 'male', 'lang' => 'multi'],
        ];
    }

    public function synthesize(string $text, array $options = []): array
    {
        $apiKey = $options['api_key'] ?? config('services.openai.api_key');
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'OpenAI API key is missing.'];
        }

        $gender = $options['gender'] ?? 'male';
        $voice = $options['voice'] ?? ($gender === 'female' ? 'nova' : 'onyx');
        $model = $options['model'] ?? 'tts-1';
        $speed = floatval($options['speed'] ?? 1.00);
        $speed = max(0.25, min(4.0, $speed));

        $disk = Storage::disk('public');
        if (!$disk->exists('audio')) {
            $disk->makeDirectory('audio');
        }

        $fileName = $options['file_name'] ?? ('news_voice_openai_' . md5($text . $voice . $speed) . '.mp3');
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
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->withHeaders(['Accept' => 'audio/mpeg'])
                ->post('https://api.openai.com/v1/audio/speech', [
                    'model'           => $model,
                    'input'           => $text,
                    'voice'           => $voice,
                    'speed'           => $speed,
                    'response_format' => 'mp3',
                ]);

            if (!$response->successful()) {
                $error = $response->json('error.message') ?? ("HTTP " . $response->status() . ": " . $response->body());
                Log::error("❌ OpenAiTtsEngine Error: " . $error);
                return ['success' => false, 'error' => $error];
            }

            file_put_contents($absolutePath, $response->body());

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
            Log::error("❌ OpenAiTtsEngine Exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
