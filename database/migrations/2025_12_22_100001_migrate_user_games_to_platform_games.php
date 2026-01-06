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
        // Skip migration if table doesn't exist or already has new structure
        // New structure = no game_name column (meaning it was created fresh)
        if (!Schema::hasTable('user_games') || !Schema::hasColumn('user_games', 'game_name')) {
            // Table was created with new structure, skip this migration
            return;
        }

        // Check if user_games table has old structure (game_name column) and has data
        $hasOldStructure = Schema::hasColumn('user_games', 'game_name') &&
                          DB::table('user_games')->count() > 0;

        // Step 1: Migrate existing games to platform_games (only if old structure exists)
        if ($hasOldStructure) {
            $driver = DB::getDriverName();
            
            if ($driver === 'mysql' || $driver === 'mariadb') {
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
            } else {
                // SQLite-compatible version
                DB::statement("
                    INSERT OR IGNORE INTO platform_games (platform, platform_game_id, name, icon_url, metadata, created_at, updated_at)
                    SELECT DISTINCT 
                        COALESCE(platform, 'playstation') as platform,
                        platform_game_id,
                        game_name as name,
                        game_icon_url as icon_url,
                        '{}' as metadata,
                        MIN(created_at) as created_at,
                        MAX(updated_at) as updated_at
                    FROM user_games
                    GROUP BY platform, platform_game_id, game_name, game_icon_url
                ");
            }
        }

        // Step 2: Add platform_game_id foreign key column (temporary, will be renamed)
        Schema::table('user_games', function (Blueprint $table) {
            $table->foreignId('platform_game_ref_id')->nullable()->after('platform_account_id')
                ->constrained('platform_games')->cascadeOnDelete();
        });

        // Step 3: Populate the foreign key (only if old structure exists)
        if ($hasOldStructure) {
            $driver = DB::getDriverName();
            
            if ($driver === 'mysql' || $driver === 'mariadb') {
                DB::statement("
                    UPDATE user_games ug
                    INNER JOIN platform_games pg ON 
                        ug.platform_game_id = pg.platform_game_id 
                        AND COALESCE(ug.platform, 'playstation') = pg.platform
                    SET ug.platform_game_ref_id = pg.id
                ");
            } else {
                // SQLite-compatible version
                DB::statement("
                    UPDATE user_games
                    SET platform_game_ref_id = (
                        SELECT pg.id
                        FROM platform_games pg
                        WHERE pg.platform_game_id = user_games.platform_game_id
                        AND pg.platform = COALESCE(user_games.platform, 'playstation')
                        LIMIT 1
                    )
                ");
            }
        }

        // Step 4: Drop old unique constraint (if it exists)
        if (Schema::hasTable('user_games')) {
            try {
                Schema::table('user_games', function (Blueprint $table) {
                    $table->dropUnique(['user_id', 'platform_account_id', 'platform_game_id']);
                });
            } catch (\Exception $e) {
                // Constraint might not exist, continue
            }
        }

        // Step 5-7: Only modify structure if old columns exist
        if ($hasOldStructure) {
            // Drop old platform_game_id string column
            $driver = DB::getDriverName();
            if ($driver === 'mysql' || $driver === 'mariadb') {
                DB::statement('ALTER TABLE user_games DROP COLUMN platform_game_id');
                DB::statement('ALTER TABLE user_games CHANGE platform_game_ref_id platform_game_id BIGINT UNSIGNED NOT NULL');
            } else {
                // SQLite doesn't support DROP COLUMN directly, use schema modification
                Schema::table('user_games', function (Blueprint $table) {
                    $table->dropColumn('platform_game_id');
                });
                // Rename is handled differently in SQLite
                DB::statement('ALTER TABLE user_games RENAME COLUMN platform_game_ref_id TO platform_game_id');
            }

            // Remove old columns
            Schema::table('user_games', function (Blueprint $table) {
                $table->dropColumn(['game_name', 'game_icon_url', 'platform']);
            });
        } else {
            // If no old structure, check if we need to rename platform_game_ref_id
            // Only rename if platform_game_ref_id exists AND platform_game_id doesn't exist
            if (Schema::hasColumn('user_games', 'platform_game_ref_id') && 
                !Schema::hasColumn('user_games', 'platform_game_id')) {
                $driver = DB::getDriverName();
                if ($driver === 'mysql' || $driver === 'mariadb') {
                    DB::statement('ALTER TABLE user_games CHANGE platform_game_ref_id platform_game_id BIGINT UNSIGNED NOT NULL');
                } else {
                    DB::statement('ALTER TABLE user_games RENAME COLUMN platform_game_ref_id TO platform_game_id');
                }
            }
        }

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
