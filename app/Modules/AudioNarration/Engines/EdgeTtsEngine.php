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
                Log::error("❌ EdgeTtsEngine Error: " . $errorMsg);
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
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        } finally {
            @unlink($tempTextFile);
            @unlink($tempPyScript);
        }
    }
}
