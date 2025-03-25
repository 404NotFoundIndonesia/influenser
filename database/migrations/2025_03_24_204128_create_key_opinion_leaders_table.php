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
        Schema::create('key_opinion_leaders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('influencer_id')->constrained()->cascadeOnDelete();
            $table->string('username');
            $table->string('platform');
            $table->string('link');
            $table->text('bio')->nullable();
            $table->float('engagement_rate')->default(0);
            $table->unsignedInteger('followers')->default(0);
            $table->unsignedInteger('following')->default(0);
            $table->unsignedInteger('total_content')->default(0);
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('likes')->default(0);
            $table->unsignedBigInteger('shares')->default(0);
            $table->unsignedBigInteger('comments')->default(0);
            $table->float('avg_views')->default(0);
            $table->float('avg_likes')->default(0);
            $table->float('avg_shares')->default(0);
            $table->float('avg_comments')->default(0);
            $table->unsignedInteger('endorsement_rate')->default(0);
            $table->json('custom')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('syncing_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('key_opinion_leaders');
    }
};
