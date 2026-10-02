<?php

namespace App\Modules\AudioNarration\Contracts;

interface TtsEngineInterface
{
    /**
     * Synthesize clean text to an MP3 audio file or binary string
     *
     * @param string $text Plain clean text to convert
     * @param array $options [voice, gender, speed, pitch, api_key, output_path]
     * @return array [success => bool, audio_path => string, audio_url => string, duration => int, error => string|null]
     */
    public function synthesize(string $text, array $options = []): array;

    /**
     * Get the provider identifier name (e.g. 'edgetts', 'openai', 'google', 'elevenlabs')
     */
    public function getProviderName(): string;

    /**
     * Get available voices for this provider
     */
    public function getAvailableVoices(string $lang = 'bn'): array;
}
