<?php

namespace App\Modules\AudioNarration\Services;

class AudioPlayerEmbedderService
{
    /**
     * Embed HTML5 Audio Player into Article Body
     *
     * @param string $content Original HTML or Plain text content
     * @param string|null $audioUrl Public URL of the audio file
     * @param string $embedMode 'top', 'after_p1', 'after_p2', 'after_p3', 'middle', 'bottom', 'manual', 'api_only', 'none'
     * @param string $title News title for player display
     * @return string Modified HTML content with embedded audio widget
     */
    public function embedPlayer(string $content, ?string $audioUrl, string $embedMode = 'top', string $title = ''): string
    {
        // If no audio or disabled embed mode
        if (empty($audioUrl) || in_array($embedMode, ['none', 'api_only'])) {
            return $this->stripShortcodes($content);
        }

        $widgetHtml = $this->renderPlayerWidget($audioUrl, $title);

        // 1. If placeholder widget is already in content, replace it with real player widget
        if (strpos($content, 'subeditor-audio-widget') !== false) {
            if (strpos($content, 'AI Voice Player Placeholder') !== false || strpos($content, '<audio') === false || strpos($content, 'src=""') !== false) {
                $content = preg_replace('/<div\s+class="subeditor-audio-widget"[^>]*>.*?<\/div>/is', $widgetHtml, $content, 1);
            }
            return $this->stripShortcodes($content);
        }

        // 2. Prioritize manual shortcode placement: [audio_player], [audio], {{audio_player}}, <!--audio_player-->
        $shortcodePattern = '/(<p\b[^>]*>\s*)?(\[audio_player\]|\[audio\]|\{\{audio_player\}\}|<!--audio_player-->)(\s*<\/p>)?/i';
        if (preg_match($shortcodePattern, $content)) {
            return preg_replace($shortcodePattern, $widgetHtml, $content, 1);
        }

        // If manual mode was selected and no shortcode was present, do not auto-inject
        if ($embedMode === 'manual') {
            return $content;
        }

        // 3. HTML paragraph-based embedding
        if (stripos($content, '<p') !== false && stripos($content, '</p>') !== false) {
            return $this->embedIntoHtmlParagraphs($content, $widgetHtml, $embedMode);
        }

        // 4. Plain Text / Newline separated embedding
        return $this->embedIntoPlainText($content, $widgetHtml, $embedMode);
    }

    /**
     * Embed into HTML content by paragraph boundary
     */
    protected function embedIntoHtmlParagraphs(string $content, string $widgetHtml, string $embedMode): string
    {
        if ($embedMode === 'top') {
            if (preg_match('/<p\b[^>]*>/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
                $offset = $matches[0][1];
                return substr($content, 0, $offset) . $widgetHtml . "\n" . substr($content, $offset);
            }
            return $widgetHtml . "\n" . $content;
        }

        if ($embedMode === 'bottom') {
            return $content . "\n\n" . $widgetHtml;
        }

        // Find all closing </p> positions
        preg_match_all('/<\/p>/i', $content, $matches, PREG_OFFSET_CAPTURE);
        $totalParagraphs = count($matches[0]);

        if ($totalParagraphs === 0) {
            return $widgetHtml . "\n" . $content;
        }

        $targetParagraphIndex = 1; // 1-indexed

        switch ($embedMode) {
            case 'after_p1':
                $targetParagraphIndex = 1;
                break;

            case 'after_p2':
                $targetParagraphIndex = min(2, $totalParagraphs);
                break;

            case 'after_p3':
                $targetParagraphIndex = min(3, $totalParagraphs);
                break;

            case 'middle':
                // Dynamic middle calculation: if 4 paragraphs, after 2; if 5, after 2 or 3
                $targetParagraphIndex = max(1, (int)floor($totalParagraphs / 2));
                break;

            default:
                $targetParagraphIndex = 1;
                break;
        }

        $matchEntry = $matches[0][$targetParagraphIndex - 1];
        $insertOffset = $matchEntry[1] + strlen($matchEntry[0]);

        return substr($content, 0, $insertOffset) . "\n\n" . $widgetHtml . "\n\n" . substr($content, $insertOffset);
    }

    /**
     * Embed into Plain text content by line break blocks
     */
    protected function embedIntoPlainText(string $content, string $widgetHtml, string $embedMode): string
    {
        if ($embedMode === 'top') {
            return $widgetHtml . "\n\n" . $content;
        }

        if ($embedMode === 'bottom') {
            return $content . "\n\n" . $widgetHtml;
        }

        $blocks = preg_split("/\n\s*\n/", trim($content));
        $totalBlocks = count($blocks);

        if ($totalBlocks <= 1) {
            return $content . "\n\n" . $widgetHtml;
        }

        $targetIndex = 1;

        switch ($embedMode) {
            case 'after_p1':
                $targetIndex = 1;
                break;
            case 'after_p2':
                $targetIndex = min(2, $totalBlocks);
                break;
            case 'after_p3':
                $targetIndex = min(3, $totalBlocks);
                break;
            case 'middle':
                $targetIndex = max(1, (int)floor($totalBlocks / 2));
                break;
            default:
                $targetIndex = 1;
                break;
        }

        array_splice($blocks, $targetIndex, 0, [$widgetHtml]);
        return implode("\n\n", $blocks);
    }

    /**
     * Remove unrendered shortcode tags and placeholder widgets from content
     */
    public function stripShortcodes(string $content): string
    {
        $content = preg_replace('/<div\s+class="subeditor-audio-widget"[^>]*>.*?AI Voice Player Placeholder.*?<\/div>/is', '', $content);
        return preg_replace('/(<p\b[^>]*>\s*)?(\[audio_player\]|\[audio\]|\{\{audio_player\}\}|<!--audio_player-->)(\s*<\/p>)?/i', '', $content);
    }

    /**
     * Render Responsive HTML5 Audio Player Template
     */
    public function renderPlayerWidget(string $audioUrl, string $title = ''): string
    {
        $safeUrl = htmlspecialchars($audioUrl, ENT_QUOTES, 'UTF-8');
        $displayTitle = !empty($title) ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : 'আজকের এই বিশেষ সংবাদটি শুনুন';

        return <<<HTML
<!-- 🎙️ SubEditor24 AI Audio Newsroom Widget -->
<div class="subeditor-audio-widget" style="margin: 20px 0; padding: 16px 20px; background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%); border: 1px solid #e0e7ff; border-radius: 16px; box-shadow: 0 4px 14px -3px rgba(99, 102, 241, 0.08); font-family: system-ui, -apple-system, sans-serif;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 32px; height: 32px; border-radius: 10px; background: #4f46e5; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: bold; box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);">
                🎧
            </div>
            <div>
                <div style="font-size: 13.5px; font-weight: 700; color: #1e293b; line-height: 1.3;">{$displayTitle}</div>
                <div style="font-size: 11px; color: #64748b; font-weight: 500;">AI Voice Narration • SubEditor24 Audio</div>
            </div>
        </div>
        <span style="font-size: 10.5px; font-weight: 800; background: #e0e7ff; color: #4338ca; padding: 3px 10px; border-radius: 20px; white-space: nowrap; text-transform: uppercase; letter-spacing: 0.5px;">
            Audio News
        </span>
    </div>
    <audio controls preload="metadata" src="{$safeUrl}" style="width: 100%; height: 40px; border-radius: 12px; outline: none;">
        <source src="{$safeUrl}" type="audio/mpeg">
        আপনার ব্রাউজার অডিও প্লেয়ার সাপোর্ট করে না।
    </audio>
</div>
<!-- /SubEditor24 AI Audio Newsroom Widget -->
HTML;
    }
}
