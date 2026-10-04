<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubscriptionNotification extends Notification
{
    use Queueable;

    protected string $title;
    protected string $message;
    protected string $link;
    protected string $type;
    protected string $icon;

    public function __construct(string $title, string $message, string $link = '', string $type = 'info', string $icon = 'fa-solid fa-receipt')
    {
        $this->title   = $title;
        $this->message = $message;
        $this->link    = $link ?: route('billing.my-subscription');
        $this->type    = $type;
        $this->icon    = $icon;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title'   => $this->title,
            'message' => $this->message,
            'link'    => $this->link,
            'type'    => $this->type,
            'icon'    => $this->icon,
            'time'    => now()->toISOString(),
        ];
    }
}
