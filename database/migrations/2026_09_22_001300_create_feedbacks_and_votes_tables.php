<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type')->default('feature'); // feature, bug, improvement
            $table->string('title');
            $table->text('description');
            $table->string('category')->default('General');
            $table->string('status')->default('under_review'); // under_review, planned, in_progress, completed, declined
            $table->text('admin_response')->nullable();
            $table->unsignedInteger('votes_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'votes_count']);
            $table->index(['type']);
        });

        Schema::create('feedback_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_id')->constrained('feedbacks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['feedback_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_votes');
        Schema::dropIfExists('feedbacks');
    }
};
