<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PricingPlan extends Model
{
    use HasFactory;

    protected $table = 'pricing_plans';

    protected $fillable = [
        'name',
        'slug',
        'badge',
        'standard_price',
        'regular_discount_price',
        'special_price',
        'special_offer_name',
        'daily_news_limit',
        'post_limit_type',
        'monthly_post_limit',
        'news_photocard_limit',
        'quotation_cards_limit',
        'reporters_limit',
        'bangla_websites_limit',
        'english_websites_limit',
        'features',
        'custom_limits',
        'is_popular',
        'is_vip',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'standard_price'         => 'decimal:2',
        'regular_discount_price' => 'decimal:2',
        'special_price'          => 'decimal:2',
        'daily_news_limit'       => 'integer',
        'monthly_post_limit'     => 'integer',
        'news_photocard_limit'   => 'integer',
        'quotation_cards_limit'  => 'integer',
        'reporters_limit'        => 'integer',
        'bangla_websites_limit'  => 'integer',
        'english_websites_limit' => 'integer',
        'features'               => 'array',
        'custom_limits'          => 'array',
        'is_popular'             => 'boolean',
        'is_vip'                 => 'boolean',
        'is_active'              => 'boolean',
        'sort_order'             => 'integer',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'pricing_plan_id');
    }

    public function subscriptionOrders()
    {
        return $this->hasMany(SubscriptionOrder::class, 'pricing_plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }

    public function scopeRegular($query)
    {
        return $query->where('is_vip', false);
    }

    public function scopeVip($query)
    {
        return $query->where('is_vip', true);
    }

    /**
     * Regular discount percentage (vs Standard)
     */
    public function getRegularDiscountPercentageAttribute(): int
    {
        if ($this->standard_price <= 0) return 0;
        $discount = (($this->standard_price - $this->regular_discount_price) / $this->standard_price) * 100;
        return (int) round($discount);
    }

    /**
     * Special discount percentage (vs Standard)
     */
    public function getSpecialDiscountPercentageAttribute(): int
    {
        if ($this->standard_price <= 0) return 0;
        $discount = (($this->standard_price - $this->special_price) / $this->standard_price) * 100;
        return (int) round($discount);
    }

    /**
     * Display label for daily news
     */
    public function getDailyNewsLabelAttribute(): string
    {
        return $this->daily_news_limit === -1 ? 'Unlimited News' : "{$this->daily_news_limit} News per Day";
    }

    /**
     * Display label for quotation cards
     */
    public function getQuotationCardsLabelAttribute(): string
    {
        return $this->quotation_cards_limit === -1 ? 'Unlimited Quotation Cards' : sprintf('%02d Quotation Cards', $this->quotation_cards_limit);
    }

    /**
     * Display label for reporters
     */
    public function getReportersLabelAttribute(): string
    {
        return $this->reporters_limit === -1 ? 'Unlimited Reporters' : "Up to {$this->reporters_limit} Reporters";
    }

    /**
     * Display label for websites
     */
    public function getWebsitesLabelAttribute(): string
    {
        if ($this->bangla_websites_limit === -1 && $this->english_websites_limit === -1) {
            return 'All Added Bangla & English Websites';
        }
        if ($this->english_websites_limit > 0) {
            return "{$this->bangla_websites_limit} Bangla + {$this->english_websites_limit} English Websites";
        }
        return "{$this->bangla_websites_limit} Bangla Websites";
    }
}
