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
        Schema::create('user_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_account_id')->constrained('platform_accounts')->cascadeOnDelete();
            $table->string('platform_game_id'); // PSN game ID (npTitleId)
            $table->string('game_name');
            $table->string('game_icon_url')->nullable();
            $table->string('platform')->default('playstation');
            
            // Game statistics
            $table->integer('total_playtime_minutes')->default(0);
            $table->integer('trophies_bronze')->default(0);
            $table->integer('trophies_silver')->default(0);
            $table->integer('trophies_gold')->default(0);
            $table->integer('trophies_platinum')->default(0);
            $table->integer('trophies_total')->default(0);
            $table->integer('trophies_earned')->default(0);
            $table->integer('trophy_progress_percentage')->default(0);
            
            // Metadata
            $table->json('metadata')->nullable();
            $table->timestamp('last_played_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'platform_account_id', 'platform_game_id']);
            $table->index('user_id');
            $table->index('platform_game_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_games');
    }
};
