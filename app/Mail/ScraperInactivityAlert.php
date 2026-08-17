<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ScraperInactivityAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $websiteName;
    public $websiteUrl;
    public $lastAttemptAt;
    public $lastScrapedAt;
    public $inactiveDuration;
    public $lastStrategy;
    public $lastHttpCode;
    public $lastError;
    public $dashboardUrl;

    public function __construct(
        $websiteName,
        $websiteUrl,
        $lastAttemptAt,
        $lastScrapedAt,
        $inactiveDuration,
        $lastStrategy,
        $lastHttpCode,
        $lastError,
        $dashboardUrl
    ) {
        $this->websiteName = $websiteName;
        $this->websiteUrl = $websiteUrl;
        $this->lastAttemptAt = $lastAttemptAt;
        $this->lastScrapedAt = $lastScrapedAt;
        $this->inactiveDuration = $inactiveDuration;
        $this->lastStrategy = $lastStrategy;
        $this->lastHttpCode = $lastHttpCode;
        $this->lastError = $lastError;
        $this->dashboardUrl = $dashboardUrl;
    }

    public function build()
    {
        return $this->subject('⚠️ [CRITICAL ALERT] Scraper Source Down: ' . $this->websiteName)
                    ->view('emails.scraper-inactivity');
    }
}
