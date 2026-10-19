<?php

namespace App\Services;

use App\Models\User;
use App\Mail\SystemAlertMail;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class SystemAlertService
{
    /**
     * Primary Super Admin Email recipient for critical/system alerts.
     */
    public const PRIMARY_ADMIN_EMAIL = 'kawsarapps72@gmail.com';

    /**
     * Dispatch an issue notification via Database (UI) and Email.
     * Guarantees that NO error or issue fails silently ("silent kill").
     *
     * @param User|int|null $user The affected user or null for system-wide/admin
     * @param string $title Short title of the issue
     * @param string $message Detailed message describing what went wrong
     * @param string $category 'social'|'publishing'|'ai'|'scraper'|'system'|'exception'
     * @param string $severity 'critical'|'error'|'warning'
     * @param string|null $link Actionable URL/route link
     * @param array|null $context Additional metadata / technical diagnostics
     * @param bool $sendEmail Whether to send an email alert
     * @param bool $notifyUi Whether to create a database notification for UI
     */
    public static function notifyIssue(
        $user,
        string $title,
        string $message,
        string $category = 'system',
        string $severity = 'error',
        ?string $link = null,
        ?array $context = [],
        bool $sendEmail = true,
        bool $notifyUi = true
    ): void {
        try {
            // 1. Resolve user model
            $userModel = null;
            if ($user instanceof User) {
                $userModel = $user;
            } elseif (is_numeric($user)) {
                $userModel = User::find($user);
            }

            // Always log with structured context
            $logPrefix = match ($severity) {
                'critical' => '🚨 [CRITICAL ALERT]',
                'warning'  => '⚠️ [SYSTEM WARNING]',
                default    => '❌ [SYSTEM ISSUE]'
            };
            Log::error("{$logPrefix} [{$category}] {$title}: {$message}", $context ?? []);

            // 2. Deliver UI Notification
            if ($notifyUi && $userModel) {
                try {
                    $userModel->notify(new SystemAlertNotification(
                        $title,
                        $message,
                        $category,
                        $severity,
                        $link,
                        null,
                        $context
                    ));
                } catch (\Throwable $notifEx) {
                    Log::warning("⚠️ Failed to store UI notification: " . $notifEx->getMessage());
                }
            }

            // If user is not super_admin, also store a copy of critical/error notification for Super Admin
            if ($notifyUi && ($severity === 'critical' || $severity === 'error')) {
                try {
                    $superAdmins = User::where('role', 'super_admin')->get();
                    foreach ($superAdmins as $admin) {
                        if (!$userModel || $admin->id !== $userModel->id) {
                            $admin->notify(new SystemAlertNotification(
                                "{$title} (User: " . ($userModel->name ?? 'System') . ")",
                                $message,
                                $category,
                                $severity,
                                $link,
                                null,
                                $context
                            ));
                        }
                    }
                } catch (\Throwable $adminNotifEx) {
                    // Ignore secondary notification error
                }
            }

            // 3. Deliver Email Alert (with intelligent rate-limiting to prevent mailbox flooding)
            if ($sendEmail) {
                self::sendAlertEmail($userModel, $title, $message, $category, $severity, $link, $context);
            }

        } catch (\Throwable $outerEx) {
            // Absolute fail-safe so alerting itself never crashes calling code
            Log::critical("🔥 SystemAlertService fail-safe caught error: " . $outerEx->getMessage());
        }
    }

    /**
     * Send email with throttling/spam prevention per unique issue signature.
     */
    protected static function sendAlertEmail($user, string $title, string $message, string $category, string $severity, ?string $link, ?array $context): void
    {
        // Compute unique signature
        $sigKey = md5(implode('|', [
            $category,
            $title,
            $context['news_id'] ?? '',
            $context['page'] ?? '',
            substr($message, 0, 80)
        ]));

        $throttleKey = "alert_email_throttle_{$sigKey}";

        // If this exact issue alert was already emailed within the last 10 minutes, skip re-emailing
        if (Cache::has($throttleKey)) {
            Log::info("ℹ️ Throttled duplicate alert email for: {$title} (Key: {$sigKey})");
            return;
        }

        // Set 10-minute cache lock
        Cache::put($throttleKey, true, now()->addMinutes(10));

        // Determine email recipients
        $recipients = collect();

        if ($user && !empty($user->email)) {
            $recipients->push($user->email);
        }

        // Always include Primary Super Admin email
        $recipients->push(self::PRIMARY_ADMIN_EMAIL);

        $recipients = $recipients->unique()->filter();

        foreach ($recipients as $email) {
            try {
                Mail::to($email)->send(new SystemAlertMail(
                    $title,
                    $message,
                    $category,
                    $severity,
                    $link,
                    $context
                ));
                Log::info("📧 System Alert Email successfully dispatched to {$email} for: {$title}");
            } catch (\Throwable $mailEx) {
                Log::warning("⚠️ Failed to dispatch alert email to {$email}: " . $mailEx->getMessage());
            }
        }
    }

    /**
     * Helper to report global unhandled 500 exceptions.
     */
    public static function notifyGlobalException(\Throwable $e, ?Request $request = null): void
    {
        $message = $e->getMessage() ?: get_class($e);
        $file = $e->getFile() . ':' . $e->getLine();
        
        $context = [
            'exception' => get_class($e),
            'location'  => $file,
            'url'       => $request ? $request->fullUrl() : 'CLI / Background',
            'method'    => $request ? $request->method() : 'N/A',
            'ip'        => $request ? $request->ip() : 'N/A',
            'user_id'   => $request && $request->user() ? $request->user()->id : 'Guest',
            'trace'     => $e->getTraceAsString(),
        ];

        self::notifyIssue(
            $request?->user(),
            "সার্ভার এরর (500 Internal Server Error)",
            "অ্যাপ্লিকেশনে একটি অপ্রত্যাশিত সার্ভার এরর ঘটেছে: {$message}",
            'exception',
            'critical',
            $context['url'],
            $context,
            true,
            true
        );
    }
}
