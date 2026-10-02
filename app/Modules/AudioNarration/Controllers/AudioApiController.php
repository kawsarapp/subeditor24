<?php

namespace App\Modules\AudioNarration\Controllers;

use App\Http\Controllers\Controller;
use App\Models\NewsItem;
use App\Modules\AudioNarration\Jobs\GenerateNewsAudioJob;
use App\Modules\AudioNarration\Services\AudioNarrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AudioApiController extends Controller
{
    protected AudioNarrationService $audioService;

    public function __construct(AudioNarrationService $audioService)
    {
        $this->audioService = $audioService;
    }

    /**
     * Generate or Regenerate Audio for a specific NewsItem
     */
    public function generate(Request $request, $id)
    {
        $news = NewsItem::findOrFail($id);

        $request->validate([
            'gender'   => 'nullable|string|in:male,female',
            'provider' => 'nullable|string|in:edgetts,openai,google,elevenlabs',
            'speed'    => 'nullable|numeric|min:0.5|max:2.0',
            'voice'    => 'nullable|string',
            'async'    => 'nullable|boolean',
        ]);

        $overrides = [
            'gender'           => $request->input('gender'),
            'provider'         => $request->input('provider'),
            'speed'            => $request->input('speed'),
            'voice'            => $request->input('voice'),
            'force_regenerate' => true,
        ];

        // If requested asynchronously via queue
        if ($request->boolean('async')) {
            GenerateNewsAudioJob::dispatch($news->id, Auth::id(), $overrides);
            return response()->json([
                'success' => true,
                'message' => '🎙️ অডিও জেনারেশন ব্যাকগ্রাউন্ডে শুরু হয়েছে...',
                'status'  => 'processing',
            ]);
        }

        // Direct synchronous synthesis for instant preview
        $result = $this->audioService->generateForNews($news, Auth::user(), $overrides);

        if ($result['success']) {
            return response()->json([
                'success'        => true,
                'message'        => '✅ এআই ভয়েস অডিও সফলভাবে তৈরি হয়েছে!',
                'audio_url'      => $result['audio_url'],
                'audio_provider' => $result['audio_provider'] ?? ($overrides['provider'] ?? 'edgetts'),
                'audio_voice'    => $result['audio_voice'] ?? ($overrides['voice'] ?? 'default'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => '❌ অডিও তৈরি করতে ব্যর্থ হয়েছে: ' . ($result['error'] ?? 'অজানা ত্রুটি'),
        ], 422);
    }

    /**
     * Check audio status for live polling in Drafts & Studio
     */
    public function status($id)
    {
        $news = NewsItem::findOrFail($id);

        return response()->json([
            'success'        => true,
            'audio_status'   => $news->audio_status ?? 'disabled',
            'audio_url'      => $news->audio_url,
            'audio_provider' => $news->audio_provider,
            'audio_voice'    => $news->audio_voice,
        ]);
    }

    /**
     * Test Voice Synthesis in Settings (Admin Tool)
     */
    public function testSynthesis(Request $request)
    {
        $request->validate([
            'provider' => 'required|string|in:edgetts,openai,google,elevenlabs',
            'gender'   => 'nullable|string|in:male,female',
            'voice'    => 'nullable|string',
            'speed'    => 'nullable|numeric|min:0.5|max:2.0',
            'sample'   => 'nullable|string|max:300',
            'api_key'  => 'nullable|string',
        ]);

        $sampleText = $request->input('sample') ?: 'স্বাগতম! সাব-এডিটর টোয়েন্টিফোর এআই ভয়েস ন্যারেশন সিস্টেম সক্রিয় রয়েছে।';
        $provider   = $request->input('provider');
        $gender     = $request->input('gender', 'male');
        $speed      = floatval($request->input('speed', 1.0));
        $voice      = $request->input('voice');
        $apiKey     = $request->input('api_key');

        $engine = $this->audioService->getEngine($provider);
        $options = [
            'gender'           => $gender,
            'voice'            => $voice,
            'speed'            => $speed,
            'api_key'          => $apiKey,
            'file_name'        => 'test_sample_' . $provider . '_' . time() . '.mp3',
            'force_regenerate' => true,
        ];

        $result = $engine->synthesize($sampleText, $options);

        if ($result['success']) {
            return response()->json([
                'success'   => true,
                'message'   => "✅ {$provider} ভয়েস টেস্ট সফল হয়েছে!",
                'audio_url' => $result['audio_url'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "❌ {$provider} টেস্ট ব্যর্থ হয়েছে: " . ($result['error'] ?? 'API ত্রুটি'),
        ], 422);
    }
}
