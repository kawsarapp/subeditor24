<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PricingPlan;
use App\Models\PricingCoupon;
use Carbon\Carbon;

class PricingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'                   => 'STARTER',
                'slug'                   => 'starter',
                'badge'                  => null,
                'standard_price'         => 35000,
                'regular_discount_price' => 20000,
                'special_price'          => 15000,
                'special_offer_name'     => 'October Special',
                'daily_news_limit'       => 50,
                'news_photocard_limit'   => -1, // Unlimited
                'quotation_cards_limit'  => 5,
                'reporters_limit'        => 10,
                'bangla_websites_limit'  => 10,
                'english_websites_limit' => 0,
                'features'               => [
                    '50 News per Day',
                    'Unlimited News Photocard',
                    '05 Quotation Cards',
                    'Unlimited Trending News Check',
                    'Viral Predictor',
                    'SEO & Website Intelligence',
                    'Analytics & ROI Dashboard',
                    'Employee Analytics & Management',
                    'Department Management',
                    'Up to 10 Reporters',
                    'Reporter News Management',
                    '10 Bangla Websites'
                ],
                'is_popular'             => false,
                'is_active'              => true,
                'sort_order'             => 1,
            ],
            [
                'name'                   => 'PROFESSIONAL',
                'slug'                   => 'professional',
                'badge'                  => null,
                'standard_price'         => 60000,
                'regular_discount_price' => 25000,
                'special_price'          => 20000,
                'special_offer_name'     => 'October Special',
                'daily_news_limit'       => 75,
                'news_photocard_limit'   => -1, // Unlimited
                'quotation_cards_limit'  => 10,
                'reporters_limit'        => 15,
                'bangla_websites_limit'  => 20,
                'english_websites_limit' => 0,
                'features'               => [
                    '75 News per Day',
                    'Unlimited News Photocard',
                    '10 Quotation Cards',
                    'Unlimited Trending News Check',
                    'Viral Predictor',
                    'SEO & Website Intelligence',
                    'Analytics & ROI Dashboard',
                    'Employee Analytics & Management',
                    'Department Management',
                    'Up to 15 Reporters',
                    'Reporter News Management',
                    '20 Bangla Websites'
                ],
                'is_popular'             => false,
                'is_active'              => true,
                'sort_order'             => 2,
            ],
            [
                'name'                   => 'BUSINESS',
                'slug'                   => 'business',
                'badge'                  => '⭐ MOST POPULAR',
                'standard_price'         => 75000,
                'regular_discount_price' => 30000,
                'special_price'          => 25000,
                'special_offer_name'     => 'October Special',
                'daily_news_limit'       => 100,
                'news_photocard_limit'   => -1, // Unlimited
                'quotation_cards_limit'  => 20,
                'reporters_limit'        => 30,
                'bangla_websites_limit'  => 20,
                'english_websites_limit' => 5,
                'features'               => [
                    '100 News per Day',
                    'Unlimited News Photocard',
                    '20 Quotation Cards',
                    'Unlimited Trending News Check',
                    'Viral Predictor',
                    'SEO & Website Intelligence',
                    'Analytics & ROI Dashboard',
                    'Employee Analytics & Management',
                    'Department Management',
                    'Up to 30 Reporters',
                    'Reporter News Management',
                    '20 Bangla + 5 English Websites'
                ],
                'is_popular'             => true,
                'is_active'              => true,
                'sort_order'             => 3,
            ],
            [
                'name'                   => 'BUSINESS PLUS',
                'slug'                   => 'business-plus',
                'badge'                  => null,
                'standard_price'         => 100000,
                'regular_discount_price' => 35000,
                'special_price'          => 30000,
                'special_offer_name'     => 'October Special',
                'daily_news_limit'       => 150,
                'news_photocard_limit'   => -1, // Unlimited
                'quotation_cards_limit'  => 30,
                'reporters_limit'        => -1, // Unlimited
                'bangla_websites_limit'  => 30,
                'english_websites_limit' => 10,
                'features'               => [
                    '150 News per Day',
                    'Unlimited News Photocard',
                    '30 Quotation Cards',
                    'Unlimited Trending News Check',
                    'Viral Predictor',
                    'SEO & Website Intelligence',
                    'Analytics & ROI Dashboard',
                    'Employee Analytics & Management',
                    'Department Management',
                    'Unlimited Reporters',
                    'Reporter News Management',
                    '30 Bangla + 10 English Websites'
                ],
                'is_popular'             => false,
                'is_active'              => true,
                'sort_order'             => 4,
            ],
            [
                'name'                   => 'ENTERPRISE',
                'slug'                   => 'enterprise',
                'badge'                  => '👑 ENTERPRISE',
                'standard_price'         => 200000,
                'regular_discount_price' => 100000,
                'special_price'          => 80000,
                'special_offer_name'     => 'October Special',
                'daily_news_limit'       => -1, // Unlimited
                'news_photocard_limit'   => -1, // Unlimited
                'quotation_cards_limit'  => -1, // Unlimited
                'reporters_limit'        => -1, // Unlimited
                'bangla_websites_limit'  => -1, // All
                'english_websites_limit' => -1, // All
                'features'               => [
                    'Unlimited News',
                    'Unlimited News Photocard',
                    'Unlimited Quotation Cards',
                    'Unlimited Trending News Check',
                    'Viral Predictor',
                    'SEO & Website Intelligence',
                    'Analytics & ROI Dashboard',
                    'Employee Analytics & Management',
                    'Unlimited Department Management',
                    'Unlimited Reporters',
                    'Unlimited Reporter News Management',
                    'All Added Bangla & English Websites'
                ],
                'is_popular'             => false,
                'is_active'              => true,
                'sort_order'             => 5,
            ],
        ];

        foreach ($plans as $planData) {
            PricingPlan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }

        // Seed Sample Coupons
        $coupons = [
            [
                'code'             => 'OCTOBER57',
                'description'      => 'October Early Bird 10% Extra Discount',
                'discount_type'    => 'percentage',
                'discount_value'   => 10,
                'min_order_amount' => 10000,
                'max_uses'         => 100,
                'expires_at'       => Carbon::now()->addMonths(1),
                'is_active'        => true,
            ],
            [
                'code'             => 'VIP2000',
                'description'      => 'Flat ৳2,000 Special Discount for Media Houses',
                'discount_type'    => 'fixed',
                'discount_value'   => 2000,
                'min_order_amount' => 20000,
                'max_uses'         => 50,
                'expires_at'       => Carbon::now()->addMonths(3),
                'is_active'        => true,
            ],
            [
                'code'             => 'EARLYBIRD',
                'description'      => 'First 20 Subscribers Special 15% Discount',
                'discount_type'    => 'percentage',
                'discount_value'   => 15,
                'min_order_amount' => 15000,
                'max_uses'         => 20,
                'expires_at'       => Carbon::now()->addMonths(2),
                'is_active'        => true,
            ]
        ];

        foreach ($coupons as $couponData) {
            PricingCoupon::updateOrCreate(
                ['code' => $couponData['code']],
                $couponData
            );
        }
    }
}
