<?php

namespace App\Modules\AudioNarration\Engines;

use App\Modules\AudioNarration\Contracts\TtsEngineInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class EdgeTtsEngine implements TtsEngineInterface
{
    public function getProviderName(): string
    {
        return 'edgetts';
    }

    public function getAvailableVoices(string $lang = 'bn'): array
    {
        return [
            'bn-BD-PradeepNeural'  => ['name' => 'Pradeep (Male, Bangladesh)', 'gender' => 'male', 'lang' => 'bn-BD'],
            'bn-BD-NabanitaNeural' => ['name' => 'Nabanita (Female, Bangladesh)', 'gender' => 'female', 'lang' => 'bn-BD'],
            'bn-IN-BashkarNeural'  => ['name' => 'Bashkar (Male, India)', 'gender' => 'male', 'lang' => 'bn-IN'],
            'bn-IN-TanishaaNeural' => ['name' => 'Tanishaa (Female, India)', 'gender' => 'female', 'lang' => 'bn-IN'],
            'en-US-GuyNeural'      => ['name' => 'Guy (Male, English US)', 'gender' => 'male', 'lang' => 'en-US'],
            'en-US-JennyNeural'    => ['name' => 'Jenny (Female, English US)', 'gender' => 'female', 'lang' => 'en-US'],
        ];
    }

    public function synthesize(string $text, array $options = []): array
    {
        $gender = $options['gender'] ?? 'male';
        $voice = $options['voice'] ?? ($gender === 'female' ? 'bn-BD-NabanitaNeural' : 'bn-BD-PradeepNeural');
        $speed = $options['speed'] ?? 1.00;
        
        // Speed conversion for Edge-TTS (e.g. 1.10 -> "+10%", 0.90 -> "-10%")
        $speedPercent = round(($speed - 1.0) * 100);
        $rateParam = ($speedPercent >= 0 ? "+{$speedPercent}%" : "{$speedPercent}%");

        // Prepare storage directory
        $disk = Storage::disk('public');
        if (!$disk->exists('audio')) {
            $disk->makeDirectory('audio');
        }

        $fileName = $options['file_name'] ?? ('news_voice_' . md5($text . $voice . $rateParam) . '.mp3');
        $relativeStoragePath = 'audio/' . $fileName;
        $absolutePath = storage_path('app/public/' . $relativeStoragePath);

        // Check if audio file already exists (MD5 cache optimization)
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

        // Write text to temporary file to avoid shell argument escaping limits
        $tempTextFile = tempnam(sys_get_temp_dir(), 'subeditor_tts_');
        file_put_contents($tempTextFile, $text);

        // Python Edge-TTS script execution
        $pythonScript = <<<PYTHON
import sys
import asyncio
import edge_tts

async def run_tts(text_path, voice, rate, output_path):
    with open(text_path, 'r', encoding='utf-8') as f:
        text = f.read()
    if not text.strip():
        sys.exit(1)
    communicate = edge_tts.Communicate(text, voice, rate=rate)
    await communicate.save(output_path)

if __name__ == '__main__':
    text_path = sys.argv[1]
    voice = sys.argv[2]
    rate = sys.argv[3]
    output_path = sys.argv[4]
    asyncio.run(run_tts(text_path, voice, rate, output_path))
PYTHON;

        $tempPyScript = tempnam(sys_get_temp_dir(), 'edge_tts_script_') . '.py';
        file_put_contents($tempPyScript, $pythonScript);

        try {
            $process = new Process(['python3', $tempPyScript, $tempTextFile, $voice, $rateParam, $absolutePath]);
            $process->setTimeout(60);
            $process->run();

            if (!$process->isSuccessful() || !file_exists($absolutePath) || filesize($absolutePath) < 512) {
                $errorMsg = $process->getErrorOutput() ?: 'Edge-TTS synthesis failed or output file empty.';
                Log::warning("⚠️ EdgeTtsEngine Error: {$errorMsg}. Attempting HTTP fallback...");

                // Attempt seamless HTTP fallback so the user always gets their audio
                $lang = str_starts_with($voice, 'en') ? 'en' : 'bn';
                if ($this->synthesizeViaHttpFallback($text, $absolutePath, $lang)) {
                    Log::info("✅ HTTP Audio Fallback succeeded for News Audio.");
                    return [
                        'success'        => true,
                        'audio_path'     => $relativeStoragePath,
                        'audio_url'      => asset('storage/' . $relativeStoragePath),
                        'audio_provider' => $this->getProviderName() . ' (fallback)',
                        'audio_voice'    => $voice,
                        'cached'         => false,
                        'error'          => null,
                    ];
                }

                if (str_contains($errorMsg, 'No module named') && str_contains($errorMsg, 'edge_tts')) {
                    $errorMsg = "সার্ভারে Python 'edge-tts' লাইব্রেরি ইনস্টল নেই। সার্ভারের টার্মিনালে রান করুন: pip install edge-tts";
                }

                return [
                    'success' => false,
                    'error'   => $errorMsg,
                ];
            }

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
            Log::error("❌ EdgeTtsEngine Exception: " . $e->getMessage());

            // Attempt seamless HTTP fallback on exception as well
            $lang = str_starts_with($voice, 'en') ? 'en' : 'bn';
            if ($this->synthesizeViaHttpFallback($text, $absolutePath, $lang)) {
                Log::info("✅ HTTP Audio Fallback succeeded after exception.");
                return [
                    'success'        => true,
                    'audio_path'     => $relativeStoragePath,
                    'audio_url'      => asset('storage/' . $relativeStoragePath),
                    'audio_provider' => $this->getProviderName() . ' (fallback)',
                    'audio_voice'    => $voice,
                    'cached'         => false,
                    'error'          => null,
                ];
            }

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        } finally {
            @unlink($tempTextFile);
            @unlink($tempPyScript);
        }
    }

    /**
     * Seamless HTTP TTS Fallback (requires 0 external Python packages)
     */
    protected function synthesizeViaHttpFallback(string $text, string $absolutePath, string $lang = 'bn'): bool
    {
        try {
            $chunks = $this->splitTextIntoChunks($text, 150);
            if (empty($chunks)) return false;

            $audioContent = '';
            foreach ($chunks as $chunk) {
                if (empty(trim($chunk))) continue;

                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Referer'    => 'https://translate.google.com/',
                ])->timeout(15)->get('https://translate.google.com/translate_tts', [
                    'ie'     => 'UTF-8',
                    'q'      => $chunk,
                    'tl'     => str_starts_with($lang, 'en') ? 'en' : 'bn',
                    'client' => 'tw-ob',
                ]);

                if ($response->successful() && strlen($response->body()) > 100) {
                    $audioContent .= $response->body();
                }
            }

            if (strlen($audioContent) > 500) {
                file_put_contents($absolutePath, $audioContent);
                return true;
            }
        } catch (\Throwable $e) {
            Log::warning("⚠️ HTTP TTS Fallback synthesis error: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Split text into chunked sentences
     */
    protected function splitTextIntoChunks(string $text, int $maxLength = 150): array
    {
        $cleanText = preg_replace('/\s+/', ' ', trim(strip_tags($text)));
        if (mb_strlen($cleanText) <= $maxLength) {
            return [$cleanText];
        }

        $sentences = preg_split('/([।!?\.\n]+)/u', $cleanText, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $chunks = [];
        $currentChunk = '';

        foreach ($sentences as $sentence) {
            if (mb_strlen($currentChunk . $sentence) <= $maxLength) {
                $currentChunk .= $sentence;
            } else {
                if (!empty(trim($currentChunk))) {
                    $chunks[] = trim($currentChunk);
                }
                $currentChunk = $sentence;
            }
        }

        if (!empty(trim($currentChunk))) {
            $chunks[] = trim($currentChunk);
        }

        return $chunks;
    }
}
