<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemAlertNotification extends Notification
{
    use Queueable;

    public string $title;
    public string $message;
    public string $category;
    public string $severity;
    public ?string $link;
    public ?string $icon;
    public ?array $context;

    /**
     * Create a new notification instance.
     *
     * @param string $title
     * @param string $message
     * @param string $category 'social'|'publishing'|'ai'|'scraper'|'system'|'exception'
     * @param string $severity 'critical'|'error'|'warning'|'info'
     * @param string|null $link
     * @param string|null $icon
     * @param array|null $context
     */
    public function __construct(
        string $title,
        string $message,
        string $category = 'system',
        string $severity = 'error',
        ?string $link = null,
        ?string $icon = null,
        ?array $context = []
    ) {
        $this->title    = $title;
        $this->message  = $message;
        $this->category = $category;
        $this->severity = $severity;
        $this->link     = $link;
        $this->context  = $context;

        // Auto-assign appropriate FontAwesome icon based on category/severity
        if ($icon) {
            $this->icon = $icon;
        } else {
            $this->icon = match ($category) {
                'social'     => 'fa-brands fa-facebook text-rose-500',
                'publishing' => 'fa-solid fa-cloud-arrow-up text-rose-500',
                'ai'         => 'fa-solid fa-wand-magic-sparkles text-amber-500',
                'scraper'    => 'fa-solid fa-spider text-amber-500',
                'exception'  => 'fa-solid fa-bug text-rose-600',
                default      => $severity === 'warning' ? 'fa-solid fa-triangle-exclamation text-amber-500' : 'fa-solid fa-circle-exclamation text-rose-500'
            };
        }
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title'    => $this->title,
            'message'  => $this->message,
            'category' => $this->category,
            'severity' => $this->severity,
            'type'     => $this->severity,
            'link'     => $this->link,
            'url'      => $this->link,
            'icon'     => $this->icon,
            'context'  => $this->context,
            'time'     => now()->toISOString()
        ];
    }
}
