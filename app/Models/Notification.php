<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notification extends Model
{
    protected $table = 'notifications';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            // Map user_id to notifiable polymorphic columns
            if (isset($model->attributes['user_id'])) {
                $userId = $model->attributes['user_id'];
                unset($model->attributes['user_id']);
                $model->notifiable_id = $userId;
                $model->notifiable_type = User::class;
            }

            // Map is_read to read_at
            if (isset($model->attributes['is_read'])) {
                $isRead = $model->attributes['is_read'];
                unset($model->attributes['is_read']);
                if ($isRead && empty($model->read_at)) {
                    $model->read_at = now();
                }
            }

            // Pack title, message, and visual cues into data column
            $data = [];
            if (isset($model->attributes['data'])) {
                $data = is_array($model->attributes['data'])
                    ? $model->attributes['data']
                    : (json_decode($model->attributes['data'], true) ?: []);
            }

            if (isset($model->attributes['title'])) {
                $data['title'] = $model->attributes['title'];
                unset($model->attributes['title']);
            }

            if (isset($model->attributes['message'])) {
                $data['message'] = $model->attributes['message'];
                unset($model->attributes['message']);
            }

            $type = $model->attributes['type'] ?? 'info';
            if (!isset($data['type'])) {
                $data['type'] = match ($type) {
                    'subscription_expired', 'subscription_expiring_critical' => 'danger',
                    'subscription_expiring_soon' => 'warning',
                    default => 'info',
                };
            }

            if (!isset($data['icon'])) {
                $data['icon'] = match ($type) {
                    'subscription_expired' => 'fa-solid fa-triangle-exclamation',
                    'subscription_expiring_critical' => 'fa-solid fa-bell',
                    'subscription_expiring_soon' => 'fa-solid fa-hourglass-half',
                    default => 'fa-solid fa-receipt',
                };
            }

            if (!isset($data['link'])) {
                try {
                    $data['link'] = route('billing.my-subscription');
                } catch (\Throwable $e) {
                    $data['link'] = '/billing/my-subscription';
                }
            }

            if (!isset($data['time'])) {
                $data['time'] = now()->toISOString();
            }

            $model->attributes['data'] = json_encode($data);
        });
    }

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function getTitleAttribute(): ?string
    {
        return $this->data['title'] ?? null;
    }

    public function getMessageAttribute(): ?string
    {
        return $this->data['message'] ?? null;
    }

    public function getUserIdAttribute(): mixed
    {
        return $this->notifiable_id;
    }

    public function getIsReadAttribute(): bool
    {
        return !is_null($this->read_at);
    }
}
