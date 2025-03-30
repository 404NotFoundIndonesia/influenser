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
        Schema::create('influencer_niche', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('influencer_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('niche_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['influencer_id', 'niche_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('influencer_niche');
    }
};
