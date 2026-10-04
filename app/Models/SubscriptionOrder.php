<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionOrder extends Model
{
    use HasFactory;

    protected $table = 'subscription_orders';

    protected $fillable = [
        'order_number',
        'user_id',
        'pricing_plan_id',
        'plan_name',
        'billing_cycle',
        'pricing_mode',
        'base_price',
        'discount_amount',
        'coupon_code',
        'final_amount',
        'payment_method',
        'sender_number',
        'transaction_id',
        'payment_proof',
        'customer_notes',
        'admin_notes',
        'status',
        'approved_by_user_id',
        'approved_at',
    ];

    protected $casts = [
        'base_price'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount'    => 'decimal:2',
        'approved_at'     => 'datetime',
    ];

    // ==========================================
    // 🔗 RELATIONSHIPS
    // ==========================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pricingPlan()
    {
        return $this->belongsTo(PricingPlan::class, 'pricing_plan_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    // ==========================================
    // 🔍 SCOPES
    // ==========================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // ==========================================
    // 🛠️ HELPER METHODS
    // ==========================================

    public static function generateOrderNumber(): string
    {
        $datePrefix = 'ORD-' . date('Ymd');
        $random = strtoupper(substr(uniqid(), -4)) . rand(10, 99);
        return "{$datePrefix}-{$random}";
    }

    public function getPaymentMethodBadgeAttribute(): string
    {
        return match ($this->payment_method) {
            'bkash'         => '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-pink-100 text-pink-700 border border-pink-200">bKash</span>',
            'nagad'         => '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-700 border border-orange-200">Nagad</span>',
            'rocket'        => '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-700 border border-purple-200">Rocket</span>',
            'bank_transfer' => '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">Bank</span>',
            default         => '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">Manual</span>',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'approved'  => '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-700 border border-emerald-300 dark:bg-emerald-950/50 dark:text-emerald-400 dark:border-emerald-800"><i class="fa-solid fa-circle-check"></i> Approved</span>',
            'rejected'  => '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-rose-100 text-rose-700 border border-rose-300 dark:bg-rose-950/50 dark:text-rose-400 dark:border-rose-800"><i class="fa-solid fa-circle-xmark"></i> Rejected</span>',
            'cancelled' => '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-slate-100 text-slate-700 border border-slate-300 dark:bg-slate-800 dark:text-slate-400"><i class="fa-solid fa-ban"></i> Cancelled</span>',
            default     => '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-950/50 dark:text-amber-400 dark:border-amber-800 animate-pulse"><i class="fa-solid fa-clock"></i> Pending Approval</span>',
        };
    }

    public function getBillingCycleLabelAttribute(): string
    {
        return match ($this->billing_cycle) {
            'monthly'     => '📅 মাসিক (৩০ দিন)',
            'yearly'      => '🌟 বার্ষিক (১২ মাস)',
            'lifetime'    => '👑 লাইফটাইম',
            'half_yearly' => '🔥 Special Offer (৬ মাস)',
            default       => '🔥 স্পেশাল অফার (৬ মাস)',
        };
    }
}
