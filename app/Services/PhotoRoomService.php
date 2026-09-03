<?php

namespace App\Services;

use App\Models\UserSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PhotoRoomService
{
    protected ?string $apiKey;
    protected string $apiUrl = 'https://image-api.photoroom.com/v2/edit';
    protected string $fallbackUrl = 'https://sdk.photoroom.com/v1/segment';
    protected string $outputDir;

    public function __construct(?int $userId = null)
    {
        $targetUserId = $userId ?? Auth::id();
        
        // 1. Fetch key from database (UserSetting with Super Admin fallback)
        $dbKey = UserSetting::getSettingWithFallback($targetUserId, 'photoroom_api_key');
        
        // 2. Fallback to config / .env
        $this->apiKey = $dbKey ?: config('services.photoroom.api_key', env('PHOTOROOM_API_KEY'));
        
        $this->outputDir = public_path('uploads/cutouts');

        if (!File::exists($this->outputDir)) {
            File::makeDirectory($this->outputDir, 0755, true);
        }
    }

    /**
     * Remove background from an image using PhotoRoom API
     *
     * @param string|\Illuminate\Http\UploadedFile $imageSource (File, Base64 data string, or URL)
     * @return array ['success' => bool, 'url' => string|null, 'error' => string|null]
     */
    public function removeBackground($imageSource): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'url' => null,
                'error' => 'PhotoRoom API key is not configured.'
            ];
        }

        try {
            $fileContent = null;
            $fileName = 'image.png';

            // 1. Handle Base64 string
            if (is_string($imageSource) && str_starts_with($imageSource, 'data:image')) {
                if (preg_match('/^data:image\/(\w+);base64,/', $imageSource, $type)) {
                    $data = substr($imageSource, strpos($imageSource, ',') + 1);
                    $fileContent = base64_decode($data);
                    $ext = strtolower($type[1]);
                    $fileName = 'image.' . ($ext === 'jpeg' ? 'jpg' : $ext);
                }
            } 
            // 2. Handle UploadedFile
            elseif ($imageSource instanceof \Illuminate\Http\UploadedFile) {
                $fileContent = file_get_contents($imageSource->getRealPath());
                $fileName = $imageSource->getClientOriginalName();
            }
            // 3. Handle local filepath or URL
            elseif (is_string($imageSource)) {
                if (filter_var($imageSource, FILTER_VALIDATE_URL)) {
                    // Fetch remote image
                    $tempResponse = Http::timeout(30)->get($imageSource);
                    if ($tempResponse->successful()) {
                        $fileContent = $tempResponse->body();
                        $fileName = basename(parse_url($imageSource, PHP_URL_PATH)) ?: 'image.png';
                    }
                } elseif (file_exists($imageSource)) {
                    $fileContent = file_get_contents($imageSource);
                    $fileName = basename($imageSource);
                } elseif (file_exists(public_path($imageSource))) {
                    $fileContent = file_get_contents(public_path($imageSource));
                    $fileName = basename($imageSource);
                }
            }

            if (!$fileContent) {
                return [
                    'success' => false,
                    'url' => null,
                    'error' => 'Unable to read source image for background removal.'
                ];
            }

            Log::info("🚀 PhotoRoom: Sending request to API (Size: " . strlen($fileContent) . " bytes)");

            // Attempt 1: Call v2 edit endpoint with imageFile
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'Accept' => 'image/png, application/json',
            ])->timeout(60)
              ->attach('imageFile', $fileContent, $fileName)
              ->post($this->apiUrl);

            // Attempt 2: Fallback to v1 segment if v2 returns error
            if (!$response->successful() && $response->status() !== 401 && $response->status() !== 403) {
                Log::warning("⚠️ PhotoRoom v2 returned status {$response->status()}. Trying v1 segment fallback...");
                $response = Http::withHeaders([
                    'x-api-key' => $this->apiKey,
                    'Accept' => 'image/png, application/json',
                ])->timeout(60)
                  ->attach('image_file', $fileContent, $fileName)
                  ->post($this->fallbackUrl);
            }

            if ($response->successful()) {
                $resultPng = $response->body();
                $outputFileName = 'cutout_' . time() . '_' . Str::random(10) . '.png';
                $outputPath = $this->outputDir . '/' . $outputFileName;

                file_put_contents($outputPath, $resultPng);

                $outputUrl = asset('uploads/cutouts/' . $outputFileName);
                Log::info("✅ PhotoRoom: Background removed successfully -> " . $outputUrl);

                return [
                    'success' => true,
                    'url' => $outputUrl,
                    'path' => $outputPath,
                    'filename' => $outputFileName,
                    'error' => null
                ];
            }

            $errorMessage = 'PhotoRoom API error (' . $response->status() . '): ' . $response->body();
            Log::error("❌ " . $errorMessage);

            $errorJson = $response->json();
            $msg = $errorJson['error']['message'] ?? ($errorJson['message'] ?? $response->body());

            return [
                'success' => false,
                'url' => null,
                'error' => 'PhotoRoom API Error: ' . $msg
            ];

        } catch (\Exception $e) {
            Log::error("🔥 PhotoRoom Exception: " . $e->getMessage());
            return [
                'success' => false,
                'url' => null,
                'error' => 'Exception during background removal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test PhotoRoom API Connection
     *
     * @param string|null $customKey
     * @return array ['success' => bool, 'message' => string]
     */
    public function testConnection(?string $customKey = null): array
    {
        $keyToTest = $customKey ?: $this->apiKey;

        if (empty($keyToTest)) {
            return [
                'success' => false,
                'message' => 'PhotoRoom API Key প্রদান করা হয়নি।'
            ];
        }

        try {
            $tinyPng = base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAA=");

            $response = Http::withHeaders([
                'x-api-key' => $keyToTest,
                'Accept'    => 'image/png, application/json',
            ])->timeout(15)
              ->attach('imageFile', $tinyPng, 'test.png')
              ->post($this->apiUrl);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => "✅ PhotoRoom API কানেকশন সফল! ব্যাকগ্রাউন্ড রিমুভাল সার্ভিস সম্পূর্ণ সক্রিয়।"
                ];
            }

            if ($response->status() === 401 || $response->status() === 403) {
                return [
                    'success' => false,
                    'message' => "❌ অথেনটিকেশন ফেইল্ড (HTTP {$response->status()})! PhotoRoom API Key ভুল বা ইনভ্যালিড।"
                ];
            }

            $err = $response->json();
            $detail = $err['error']['message'] ?? ($err['message'] ?? 'Status ' . $response->status());

            return [
                'success' => false,
                'message' => "❌ PhotoRoom কানেকশন ব্যর্থ: " . $detail
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '❌ কানেকশন এরর: ' . $e->getMessage()
            ];
        }
    }
}
