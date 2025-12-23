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
     * This migration:
     * 1. Creates platform_games entries from existing user_games
     * 2. Adds platform_game_id foreign key to user_games
     * 3. Removes redundant columns (game_name, game_icon_url, platform)
     */
    public function up(): void
    {
        // Step 1: Migrate existing games to platform_games
        DB::statement("
            INSERT INTO platform_games (platform, platform_game_id, name, icon_url, metadata, created_at, updated_at)
            SELECT DISTINCT 
                COALESCE(platform, 'playstation') as platform,
                platform_game_id,
                game_name as name,
                game_icon_url as icon_url,
                JSON_OBJECT() as metadata,
                MIN(created_at) as created_at,
                MAX(updated_at) as updated_at
            FROM user_games
            GROUP BY platform, platform_game_id, game_name, game_icon_url
            ON DUPLICATE KEY UPDATE updated_at = VALUES(updated_at)
        ");

        // Step 2: Add platform_game_id foreign key column (temporary, will be renamed)
        Schema::table('user_games', function (Blueprint $table) {
            $table->foreignId('platform_game_ref_id')->nullable()->after('platform_account_id')
                ->constrained('platform_games')->cascadeOnDelete();
        });

        // Step 3: Populate the foreign key
        DB::statement("
            UPDATE user_games ug
            INNER JOIN platform_games pg ON 
                ug.platform_game_id = pg.platform_game_id 
                AND COALESCE(ug.platform, 'playstation') = pg.platform
            SET ug.platform_game_ref_id = pg.id
        ");

        // Step 4: Drop old unique constraint
        Schema::table('user_games', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'platform_account_id', 'platform_game_id']);
        });

        // Step 5: Drop old platform_game_id string column
        DB::statement('ALTER TABLE user_games DROP COLUMN platform_game_id');

        // Step 6: Rename platform_game_ref_id to platform_game_id
        DB::statement('ALTER TABLE user_games CHANGE platform_game_ref_id platform_game_id BIGINT UNSIGNED NOT NULL');

        // Step 7: Remove old columns
        Schema::table('user_games', function (Blueprint $table) {
            $table->dropColumn(['game_name', 'game_icon_url', 'platform']);
        });

        // Step 8: Add unique constraint back
        Schema::table('user_games', function (Blueprint $table) {
            $table->unique(['user_id', 'platform_account_id', 'platform_game_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back old columns
        Schema::table('user_games', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'platform_account_id', 'platform_game_id']);
            $table->dropForeign(['platform_game_id']);
            $table->string('platform_game_id_string')->after('platform_account_id');
            $table->string('game_name')->after('platform_game_id_string');
            $table->string('game_icon_url')->nullable()->after('game_name');
            $table->string('platform')->default('playstation')->after('game_icon_url');
        });

        // Populate from platform_games
        DB::statement("
            UPDATE user_games ug
            INNER JOIN platform_games pg ON ug.platform_game_id = pg.id
            SET 
                ug.platform_game_id_string = pg.platform_game_id,
                ug.game_name = pg.name,
                ug.game_icon_url = pg.icon_url,
                ug.platform = pg.platform
        ");

        // Drop foreign key and rename
        Schema::table('user_games', function (Blueprint $table) {
            $table->dropForeign(['platform_game_id']);
        });
        
        DB::statement('ALTER TABLE user_games DROP COLUMN platform_game_id');
        DB::statement('ALTER TABLE user_games CHANGE platform_game_id_string platform_game_id VARCHAR(255) NOT NULL');
        
        // Add unique constraint back
        Schema::table('user_games', function (Blueprint $table) {
            $table->unique(['user_id', 'platform_account_id', 'platform_game_id']);
        });
    }
};
