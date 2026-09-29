<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pricing Plans Table
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // e.g. STARTER, PROFESSIONAL, BUSINESS
            $table->string('slug')->unique();            // e.g. starter, professional, business, business-plus, enterprise
            $table->string('badge')->nullable();         // e.g. 🔥 October Special, ⭐ MOST POPULAR, 👑 ENTERPRISE
            $table->decimal('standard_price', 10, 2);    // e.g. 35000.00
            $table->decimal('regular_discount_price', 10, 2); // e.g. 20000.00
            $table->decimal('special_price', 10, 2);     // e.g. 15000.00
            $table->string('special_offer_name')->default('October Special'); // e.g. October Special
            $table->integer('daily_news_limit')->default(50); // -1 for unlimited
            $table->integer('news_photocard_limit')->default(-1); // -1 for unlimited
            $table->integer('quotation_cards_limit')->default(5); // -1 for unlimited
            $table->integer('reporters_limit')->default(10); // -1 for unlimited
            $table->integer('bangla_websites_limit')->default(10); // -1 for unlimited/all
            $table->integer('english_websites_limit')->default(0); // -1 for unlimited/all
            $table->json('features')->nullable();        // JSON array of highlighted features
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Pricing Coupons Table
        Schema::create('pricing_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();            // e.g. OCTOBER57, EARLYBIRD
            $table->string('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('discount_value', 10, 2);   // e.g. 10 (%) or 2000 (BDT)
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->integer('max_uses')->nullable();     // null for unlimited
            $table->integer('uses_count')->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->json('allowed_plans')->nullable();   // null for all plans, or array of slugs
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_coupons');
        Schema::dropIfExists('pricing_plans');
    }
};
