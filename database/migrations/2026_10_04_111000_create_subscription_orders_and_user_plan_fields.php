<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add subscription columns to users table if missing
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'pricing_plan_id')) {
                $table->foreignId('pricing_plan_id')->nullable()->after('role')->constrained('pricing_plans')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'subscription_status')) {
                $table->string('subscription_status')->default('trial')->after('pricing_plan_id'); // trial, active, expired, pending_approval, lifetime
            }
            if (!Schema::hasColumn('users', 'subscription_cycle')) {
                $table->string('subscription_cycle')->nullable()->after('subscription_status'); // monthly, half_yearly, yearly, lifetime
            }
        });

        // 2. Create subscription_orders table
        if (!Schema::hasTable('subscription_orders')) {
            Schema::create('subscription_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number')->unique(); // e.g. ORD-20261004-8742
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('pricing_plan_id')->nullable()->constrained('pricing_plans')->nullOnDelete();
                $table->string('plan_name');
                $table->string('billing_cycle')->default('half_yearly'); // monthly, half_yearly, yearly, lifetime
                $table->string('pricing_mode')->default('special'); // special, regular, standard
                $table->decimal('base_price', 10, 2);
                $table->decimal('discount_amount', 10, 2)->default(0.00);
                $table->string('coupon_code')->nullable();
                $table->decimal('final_amount', 10, 2);
                $table->string('payment_method')->default('bkash'); // bkash, nagad, rocket, bank_transfer, manual
                $table->string('sender_number')->nullable();
                $table->string('transaction_id')->nullable(); // TrxID
                $table->string('payment_proof')->nullable(); // screenshot path
                $table->text('customer_notes')->nullable();
                $table->text('admin_notes')->nullable();
                $table->string('status')->default('pending'); // pending, approved, rejected, cancelled
                $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index('order_number');
                $table->index('transaction_id');
            });
        }

        // 3. Add manual payment settings to user_settings table
        Schema::table('user_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('user_settings', 'manual_payment_methods')) {
                $table->json('manual_payment_methods')->nullable()->after('vip_pricing_page_config');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_orders');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'subscription_cycle')) {
                $table->dropColumn('subscription_cycle');
            }
            if (Schema::hasColumn('users', 'subscription_status')) {
                $table->dropColumn('subscription_status');
            }
            if (Schema::hasColumn('users', 'pricing_plan_id')) {
                $table->dropForeign(['pricing_plan_id']);
                $table->dropColumn('pricing_plan_id');
            }
        });

        Schema::table('user_settings', function (Blueprint $table) {
            if (Schema::hasColumn('user_settings', 'manual_payment_methods')) {
                $table->dropColumn('manual_payment_methods');
            }
        });
    }
};
