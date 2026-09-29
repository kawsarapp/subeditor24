<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class PricingCoupon extends Model
{
    use HasFactory;

    protected $table = 'pricing_coupons';

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_uses',
        'uses_count',
        'starts_at',
        'expires_at',
        'allowed_plans',
        'is_active',
    ];

    protected $casts = [
        'discount_value'   => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_uses'         => 'integer',
        'uses_count'       => 'integer',
        'starts_at'        => 'datetime',
        'expires_at'       => 'datetime',
        'allowed_plans'    => 'array',
        'is_active'        => 'boolean',
    ];

    /**
     * Check if coupon is valid for a given plan and price
     */
    public function isValidForPlan(string $planSlug, float $price): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'এই কুপনটি বর্তমানে সক্রিয় নয়।'];
        }

        $now = Carbon::now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return ['valid' => false, 'message' => 'এই কুপনটির মেয়াদ এখনো শুরু হয়নি।'];
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return ['valid' => false, 'message' => 'এই কুপনটির মেয়াদ শেষ হয়ে গেছে।'];
        }

        if ($this->max_uses && $this->uses_count >= $this->max_uses) {
            return ['valid' => false, 'message' => 'এই কুপনটির ব্যবহারের সর্বোচ্চ সীমা শেষ হয়ে গেছে।'];
        }

        if ($this->min_order_amount > 0 && $price < $this->min_order_amount) {
            return ['valid' => false, 'message' => "এই কুপনটি ব্যবহারের জন্য ন্যূনতম ৳" . number_format($this->min_order_amount) . " এর প্যাকেজ সিলেক্ট করতে হবে।"];
        }

        if (!empty($this->allowed_plans) && !in_array($planSlug, $this->allowed_plans)) {
            return ['valid' => false, 'message' => 'এই কুপনটি নির্বাচিত প্যাকেজে প্রযোজ্য নয়।'];
        }

        // Calculate discount amount
        $discountAmount = 0;
        if ($this->discount_type === 'percentage') {
            $discountAmount = ($price * $this->discount_value) / 100;
        } else {
            $discountAmount = min($this->discount_value, $price);
        }

        $finalPrice = max(0, $price - $discountAmount);

        return [
            'valid'           => true,
            'code'            => $this->code,
            'discount_type'   => $this->discount_type,
            'discount_value'  => (float) $this->discount_value,
            'discount_amount' => (float) round($discountAmount, 2),
            'original_price'  => (float) $price,
            'final_price'     => (float) round($finalPrice, 2),
            'message'         => '🎉 কুপন সফলভাবে অ্যাপ্লাই হয়েছে!',
        ];
    }
}
