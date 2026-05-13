<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_key_opinion_leader', function (Blueprint $table) {
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('key_opinion_leader_id')->constrained()->cascadeOnDelete();
            $table->string('deliverable')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->unsignedBigInteger('actual_views')->nullable();
            $table->unsignedBigInteger('actual_likes')->nullable();
            $table->unsignedBigInteger('actual_comments')->nullable();
            $table->unsignedBigInteger('actual_shares')->nullable();
            $table->timestamps();

            $table->primary(['campaign_id', 'key_opinion_leader_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_key_opinion_leader');
    }
};
