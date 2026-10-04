<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'role', 
		'parent_id',
        'pricing_plan_id',
        'subscription_status',
        'subscription_cycle',
        'credits', 
        'total_credits_limit', 
        'daily_post_limit',
        'post_limit_type',
        'monthly_post_limit',
        'daily_bg_remove_limit',
        'daily_crawl_limit',
        'daily_ai_limit',
        'is_active',
        'staff_limit',
        'permissions',
        'last_login_at',
        'author_signature',
        'signature_placement',
        'department_id',
        'designation_id',
        'joining_date',
        'expire_date',
        'district',
        'upazila',
        'working_location',
        'phone',
        'nid',
        'emergency_contact',
        'blood_group',
        'present_address',
        'permanent_address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
		'permissions' => 'array',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'joining_date' => 'date',
        'expire_date' => 'date',
    ];

    // ==========================================
    // 🔥 RELATIONSHIPS
    // ==========================================

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function settings()
    {
        return $this->hasOne(UserSetting::class);
    }

    public function newsItems()
    {
        return $this->hasMany(NewsItem::class);
    }

    public function accessibleWebsites()
    {
        return $this->belongsToMany(Website::class, 'user_website', 'user_id', 'website_id');
    }
    
    public function creditHistories()
    {
        return $this->hasMany(CreditHistory::class)->latest();
    }

    public function websites() 
    { 
        return $this->hasMany(Website::class); 
    }

    // ইউজারের সকল Facebook Pages
    public function facebookPages()
    {
        return $this->hasMany(FacebookPage::class);
    }

    // ==========================================
    // 🔥 HELPER FUNCTIONS
    // ==========================================

    public function bgRemoveLogs()
    {
        return $this->hasMany(BgRemoveLog::class)->latest();
    }

    public function hasDailyBgRemoveLimitRemaining()
    {
        if ($this->role === 'super_admin') return true;

        $todayRemovals = $this->bgRemoveLogs()
            ->where('status', 'success')
            ->whereDate('created_at', now())
            ->count();

        return $todayRemovals < ($this->daily_bg_remove_limit ?? 20);
    }

    public function getTodaysBgRemoveCountAttribute()
    {
        return $this->bgRemoveLogs()
            ->where('status', 'success')
            ->whereDate('created_at', now())
            ->count();
    }

    /**
     * Check if user has remaining post limit (supports both daily and monthly mode)
     */
    public function hasPostLimitRemaining(): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        // If staff/reporter, check parent's limit
        if (in_array($this->role, ['staff', 'reporter']) && $this->parent_id) {
            $parent = $this->parent;
            return $parent ? $parent->hasPostLimitRemaining() : false;
        }

        if ($this->post_limit_type === 'monthly') {
            $limit = $this->monthly_post_limit ?? (($this->daily_post_limit ?? 20) * 30);
            if ($limit >= 99999) return true; // unlimited
            return $this->this_month_post_count < $limit;
        }

        // Daily limit mode (default)
        $limit = $this->daily_post_limit ?? 10;
        if ($limit >= 9999) return true; // unlimited
        return $this->todays_post_count < $limit;
    }

    /**
     * Backward-compatible wrapper for hasPostLimitRemaining
     */
    public function hasDailyLimitRemaining(): bool
    {
        return $this->hasPostLimitRemaining();
    }

    public function hasCredits(): bool
    {
        if ($this->role === 'super_admin') return true;
        return $this->credits > 0;
    }

    public function getTodaysPostCountAttribute(): int
    {
        return (int) $this->newsItems()
            ->withoutGlobalScopes()
            ->where('is_posted', true)
            ->where(function($q) {
                $q->whereDate('posted_at', \Carbon\Carbon::now())
                  ->orWhereDate('updated_at', \Carbon\Carbon::now());
            })
            ->count();
    }

    /**
     * Get count of posts published this current calendar month
     */
    public function getThisMonthPostCountAttribute(): int
    {
        return (int) $this->newsItems()
            ->withoutGlobalScopes()
            ->where('is_posted', true)
            ->whereYear('posted_at', now()->year)
            ->whereMonth('posted_at', now()->month)
            ->count();
    }

    /**
     * Get active limit value (number)
     */
    public function getActivePostLimitAttribute(): int
    {
        if ($this->post_limit_type === 'monthly') {
            return (int) ($this->monthly_post_limit ?? (($this->daily_post_limit ?? 20) * 30));
        }
        return (int) ($this->daily_post_limit ?? 10);
    }

    /**
     * Get count of posts made in active limit period (today or this month)
     */
    public function getActivePeriodPostCountAttribute(): int
    {
        if ($this->post_limit_type === 'monthly') {
            return $this->this_month_post_count;
        }
        return $this->todays_post_count;
    }

    /**
     * Bengali/English human label for active limit type
     */
    public function getPostLimitLabelAttribute(): string
    {
        if ($this->post_limit_type === 'monthly') {
            $lim = $this->active_post_limit;
            return ($lim >= 99999) ? 'মাসিক আনলিমিটেড' : "{$lim} পোস্ট / মাস";
        }
        $lim = $this->active_post_limit;
        return ($lim >= 9999) ? 'দৈনিক আনলিমিটেড' : "{$lim} পোস্ট / দিন";
    }

    /**
     * Error message when post limit is reached
     */
    public function getPostLimitErrorMessage(): string
    {
        if ($this->post_limit_type === 'monthly') {
            return "❌ আপনার এই মাসের নির্ধারিত পোস্ট লিমিট ({$this->active_post_limit} টি) শেষ হয়ে গেছে!";
        }
        return "❌ আপনার আজকের দৈনিক পোস্ট লিমিট ({$this->active_post_limit} টি) শেষ হয়ে গেছে!";
    }
	
	public function reporters()
	{
		return $this->hasMany(User::class, 'parent_id');
	}

	public function parent()
	{
		return $this->belongsTo(User::class, 'parent_id');
	}
	
	public function hasPermission($permission)
    {
        if ($this->role === 'super_admin') return true;
        return is_array($this->permissions) && in_array($permission, $this->permissions);
    }

    // ==========================================
    // 👑 SUBSCRIPTION & BILLING METHODS
    // ==========================================

    public function pricingPlan()
    {
        return $this->belongsTo(PricingPlan::class, 'pricing_plan_id');
    }

    public function subscriptionOrders()
    {
        return $this->hasMany(SubscriptionOrder::class)->latest();
    }

    /**
     * Check if user's subscription is active
     */
    public function isSubscriptionActive(): bool
    {
        // 1. Super Admin is always active
        if ($this->role === 'super_admin') {
            return true;
        }

        // 2. Staff/Reporter inherits status from parent
        if (in_array($this->role, ['staff', 'reporter']) && $this->parent_id) {
            $parent = $this->parent;
            return $parent ? $parent->isSubscriptionActive() : false;
        }

        // 3. Lifetime plans
        if ($this->subscription_status === 'lifetime') {
            return true;
        }

        // 4. Inactive or explicitly expired flag
        if ($this->subscription_status === 'expired' || $this->is_active === false) {
            return false;
        }

        // 5. Check expire_date
        if ($this->expire_date) {
            return \Carbon\Carbon::parse($this->expire_date)->endOfDay()->isFuture() || \Carbon\Carbon::parse($this->expire_date)->isToday();
        }

        // 6. Default trial without explicit expire_date: fallback to 7 days from created_at
        if ($this->created_at) {
            return $this->created_at->addDays(7)->isFuture();
        }

        return true;
    }

    /**
     * Number of days used in current subscription
     */
    public function getDaysUsedAttribute(): int
    {
        $startDate = $this->joining_date ? \Carbon\Carbon::parse($this->joining_date)->startOfDay() : ($this->created_at ? $this->created_at->startOfDay() : now()->startOfDay());
        $now = now()->startOfDay();

        if ($startDate->isFuture()) {
            return 0;
        }

        return (int) $startDate->diffInDays($now);
    }

    /**
     * Number of days remaining in current subscription
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->role === 'super_admin' || $this->subscription_status === 'lifetime') {
            return 999;
        }

        $expireDate = $this->expire_date ? \Carbon\Carbon::parse($this->expire_date)->endOfDay() : ($this->created_at ? $this->created_at->addDays(7)->endOfDay() : null);

        if (!$expireDate) {
            return 0;
        }

        if ($expireDate->isPast() && !$expireDate->isToday()) {
            return 0;
        }

        return (int) ceil(now()->diffInDays($expireDate, false));
    }

    /**
     * Total cycle days of current plan (e.g. 30, 180, 365)
     */
    public function getTotalPlanDaysAttribute(): int
    {
        if ($this->joining_date && $this->expire_date) {
            $start = \Carbon\Carbon::parse($this->joining_date)->startOfDay();
            $end = \Carbon\Carbon::parse($this->expire_date)->endOfDay();
            $diff = (int) $start->diffInDays($end);
            return $diff > 0 ? $diff : 30;
        }

        return match ($this->subscription_cycle) {
            'yearly'      => 365,
            'half_yearly' => 180,
            'lifetime'    => 3650,
            default       => 30,
        };
    }

    /**
     * User friendly Bangla/English label for subscription cycle
     */
    public function getSubscriptionCycleLabelAttribute(): string
    {
        if ($this->subscription_status === 'lifetime') {
            return '👑 লাইফটাইম প্যাকেজ';
        }
        if ($this->subscription_status === 'trial' || $this->subscription_cycle === 'trial') {
            return '🎁 ফ্রি ট্রায়াল (৭ দিন)';
        }
        return match ($this->subscription_cycle) {
            'monthly'     => '📅 মাসিক প্যাকেজ (৩০ দিন)',
            'yearly'      => '🌟 বার্ষিক প্যাকেজ (১২ মাস)',
            'half_yearly' => '🔥 Special Offer (৬ মাস মেয়াদ)',
            default       => '🔥 স্পেশাল অফার (৬ মাস মেয়াদ)',
        };
    }

    /**
     * Progress percentage of days passed (0% to 100%)
     */
    public function getSubscriptionProgressPercentAttribute(): float
    {
        $total = $this->total_plan_days;
        if ($total <= 0) return 100.0;

        $used = $this->days_used;
        $percent = ($used / $total) * 100;

        return (float) min(100.0, max(0.0, round($percent, 1)));
    }

    /**
     * Check if subscription expires within given days
     */
    public function isExpiringSoon(int $days = 3): bool
    {
        if ($this->role === 'super_admin' || $this->subscription_status === 'lifetime') {
            return false;
        }

        $remaining = $this->days_remaining;
        return $remaining > 0 && $remaining <= $days;
    }

    /**
     * Check if subscription has already expired
     */
    public function isExpired(): bool
    {
        return !$this->isSubscriptionActive();
    }

    /**
     * Activate or renew a pricing plan for the user
     */
    public function activatePlan(PricingPlan $plan, string $cycle = 'half_yearly', ?SubscriptionOrder $order = null): void
    {
        $durationDays = match ($cycle) {
            'monthly'     => 30,
            'yearly'      => 365,
            'lifetime'    => 3650,
            default       => 180, // half_yearly / 6 months default
        };

        // If user already has an active subscription of same or higher, extend from existing expire_date
        $baseDate = ($this->expire_date && \Carbon\Carbon::parse($this->expire_date)->isFuture())
            ? \Carbon\Carbon::parse($this->expire_date)
            : now();

        $this->pricing_plan_id = $plan->id;
        $this->subscription_status = ($cycle === 'lifetime') ? 'lifetime' : 'active';
        $this->subscription_cycle = $cycle;
        $this->joining_date = $this->joining_date ?: now();
        $this->expire_date = ($cycle === 'lifetime') ? now()->addYears(10) : $baseDate->copy()->addDays($durationDays);
        $this->is_active = true;

        // Sync limits from plan
        if ($plan->post_limit_type) {
            $this->post_limit_type = $plan->post_limit_type;
        }
        if ($plan->monthly_post_limit !== null) {
            $this->monthly_post_limit = $plan->monthly_post_limit;
        }
        if ($plan->daily_news_limit !== null && $plan->daily_news_limit > 0) {
            $this->daily_post_limit = $plan->daily_news_limit;
            $this->daily_ai_limit = $plan->daily_news_limit;
        }
        if ($plan->reporters_limit !== null && $plan->reporters_limit > 0) {
            $this->staff_limit = $plan->reporters_limit;
        }

        // Add / Refill AI Credits
        $planCredits = $plan->custom_limits['ai_credits'] ?? ($plan->daily_news_limit > 0 ? ($plan->daily_news_limit * 30) : 500);
        $this->credits = max((int) $this->credits, (int) $planCredits);
        $this->total_credits_limit = max((int) $this->total_credits_limit, (int) $planCredits);

        // Ensure key permissions are enabled
        $currentPerms = is_array($this->permissions) ? $this->permissions : [];
        $basePerms = ['can_scrape', 'can_ai', 'can_studio', 'can_direct_publish', 'can_view_published', 'can_auto_post', 'can_central_feed', 'can_custom_photo_card'];
        $this->permissions = array_values(array_unique(array_merge($currentPerms, $basePerms)));

        $this->save();

        // Log credit history
        try {
            CreditHistory::create([
                'user_id'         => $this->id,
                'action_type'     => 'plan_activation',
                'description'     => "Plan Activated: {$plan->name} ({$cycle})",
                'credits_change'  => $planCredits,
                'balance_after'   => $this->credits,
            ]);
        } catch (\Exception $e) {}
    }
}
