<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\NewsItem;
use App\Services\SystemAlertService;
use App\Mail\SystemAlertMail;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SystemAlertServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_alert_service_dispatches_email_and_database_notification()
    {
        Mail::fake();
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'role'  => 'client'
        ]);

        SystemAlertService::notifyIssue(
            $user,
            'ফেসবুক পোস্ট ব্যর্থ',
            'নিউজ টোকেন মেয়াদোত্তীর্ণ হওয়ায় পোস্ট করা যায়নি।',
            'social',
            'error',
            '/settings',
            ['page' => 'News Portal Page']
        );

        // Verify Database Notification sent to user
        Notification::assertSentTo($user, SystemAlertNotification::class, function ($notification) use ($user) {
            $data = $notification->toArray($user);
            return $data['title'] === 'ফেসবুক পোস্ট ব্যর্থ' &&
                   $data['category'] === 'social' &&
                   $data['severity'] === 'error';
        });

        // Verify Email sent to user and primary admin
        Mail::assertSent(SystemAlertMail::class, function ($mail) {
            return $mail->alertTitle === 'ফেসবুক পোস্ট ব্যর্থ' &&
                   $mail->category === 'social' &&
                   $mail->severity === 'error';
        });
    }

    public function test_system_alert_throttling_prevents_duplicate_email_flooding()
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'throttletest@example.com'
        ]);

        // Call twice with identical signature
        SystemAlertService::notifyIssue(
            $user,
            'একই এরর অ্যালার্ট',
            'একই ডুপ্লিকেট মেসেজ যা দ্রুত একাধিকবার হতে পারে।',
            'publishing',
            'error',
            null,
            ['news_id' => 999]
        );

        SystemAlertService::notifyIssue(
            $user,
            'একই এরর অ্যালার্ট',
            'একই ডুপ্লিকেট মেসেজ যা দ্রুত একাধিকবার হতে পারে।',
            'publishing',
            'error',
            null,
            ['news_id' => 999]
        );

        // Should only be sent once due to 10-min cache lock
        Mail::assertSent(SystemAlertMail::class, 2); // 1 to user + 1 to primary admin on first dispatch, 0 on second dispatch
    }

    public function test_notifications_read_endpoint_marks_notifications_as_read()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $user->notify(new SystemAlertNotification('টেস্ট এরর', 'মেসেজ', 'system', 'error'));
        $this->assertEquals(1, $user->unreadNotifications()->count());

        $response = $this->getJson(route('notifications.read'));
        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertEquals(0, $user->fresh()->unreadNotifications()->count());
    }
}
