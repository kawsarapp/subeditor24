<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SystemAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $alertTitle;
    public string $alertMessage;
    public string $category;
    public string $severity;
    public ?string $actionUrl;
    public ?array $context;
    public string $appName;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $alertTitle,
        string $alertMessage,
        string $category = 'system',
        string $severity = 'error',
        ?string $actionUrl = null,
        ?array $context = []
    ) {
        $this->alertTitle   = $alertTitle;
        $this->alertMessage = $alertMessage;
        $this->category     = $category;
        $this->severity     = $severity;
        $this->actionUrl    = $actionUrl;
        $this->context      = $context ?? [];
        $this->appName      = config('app.name', 'Subeditor24');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $severityBadge = match ($this->severity) {
            'critical' => '🔴 [CRITICAL]',
            'warning'  => '🟡 [WARNING]',
            default    => '⚠️ [ALERT]'
        };

        $categoryPrefix = strtoupper($this->category);

        return $this->subject("{$severityBadge} {$categoryPrefix}: {$this->alertTitle}")
                    ->view('emails.system-alert');
    }
}
