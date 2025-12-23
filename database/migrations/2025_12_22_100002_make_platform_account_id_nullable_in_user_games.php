<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Makes platform_account_id nullable to allow manual game additions
     * without requiring a platform account.
     */
    public function up(): void
    {
        Schema::table('user_games', function (Blueprint $table) {
            // Drop the unique constraint first
            $table->dropUnique(['user_id', 'platform_account_id', 'platform_game_id']);
        });

        // Make platform_account_id nullable
        DB::statement('ALTER TABLE user_games MODIFY platform_account_id BIGINT UNSIGNED NULL');

        // Add unique constraint back (MySQL allows multiple NULLs in unique constraints)
        Schema::table('user_games', function (Blueprint $table) {
            $table->unique(['user_id', 'platform_account_id', 'platform_game_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_games', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'platform_account_id', 'platform_game_id']);
        });

        // Make platform_account_id not nullable
        DB::statement('ALTER TABLE user_games MODIFY platform_account_id BIGINT UNSIGNED NOT NULL');

        Schema::table('user_games', function (Blueprint $table) {
            $table->unique(['user_id', 'platform_account_id', 'platform_game_id']);
        });
    }
};
