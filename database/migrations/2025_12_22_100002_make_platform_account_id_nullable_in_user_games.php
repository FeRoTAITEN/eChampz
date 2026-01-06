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
        $driver = DB::getDriverName();
        
        Schema::table('user_games', function (Blueprint $table) {
            // Drop the unique constraint first
            $table->dropUnique(['user_id', 'platform_account_id', 'platform_game_id']);
        });

        // Make platform_account_id nullable - use different syntax for SQLite vs MySQL
        if ($driver === 'sqlite') {
            // SQLite doesn't support MODIFY, so we need to recreate the table
            // Get table structure using PRAGMA
            $tableInfo = DB::select("PRAGMA table_info(user_games)");
            
            // Build CREATE TABLE statement
            $columns = [];
            $columnNames = [];
            foreach ($tableInfo as $col) {
                $columnNames[] = $col->name;
                if ($col->name === 'id') {
                    $columns[] = 'id INTEGER PRIMARY KEY AUTOINCREMENT';
                } elseif ($col->name === 'platform_account_id') {
                    $columns[] = 'platform_account_id INTEGER NULL';
                } else {
                    $type = $col->type;
                    $notNull = $col->notnull ? 'NOT NULL' : 'NULL';
                    $default = '';
                    if ($col->dflt_value !== null) {
                        // Remove quotes if present (SQLite returns quoted strings)
                        $value = trim($col->dflt_value, "'\"");
                        if (is_numeric($value)) {
                            $default = "DEFAULT {$value}";
                        } else {
                            $default = "DEFAULT '{$value}'";
                        }
                    }
                    $columns[] = trim("{$col->name} {$type} {$notNull} {$default}");
                }
            }
            
            $columnDefs = implode(', ', $columns);
            $columnList = implode(', ', $columnNames);
            
            // Step 1: Create new table
            DB::statement("CREATE TABLE user_games_new ({$columnDefs})");
            
            // Step 2: Copy data
            DB::statement("INSERT INTO user_games_new ({$columnList}) SELECT {$columnList} FROM user_games");
            
            // Step 3: Drop old table
            Schema::drop('user_games');
            
            // Step 4: Rename new table
            DB::statement('ALTER TABLE user_games_new RENAME TO user_games');
            
            // Step 5: Recreate foreign keys and indexes
            Schema::table('user_games', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('platform_account_id')->references('id')->on('platform_accounts')->onDelete('cascade');
                $table->foreign('platform_game_id')->references('id')->on('platform_games')->onDelete('cascade');
                $table->index('user_id');
                $table->index('platform_game_id');
            });
        } else {
            // MySQL/MariaDB syntax
            DB::statement('ALTER TABLE user_games MODIFY platform_account_id BIGINT UNSIGNED NULL');
        }

        // Add unique constraint back (MySQL allows multiple NULLs in unique constraints)
        // For SQLite, it's already in the new table, so skip
        if ($driver !== 'sqlite') {
            Schema::table('user_games', function (Blueprint $table) {
                $table->unique(['user_id', 'platform_account_id', 'platform_game_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        Schema::table('user_games', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'platform_account_id', 'platform_game_id']);
        });

        // Make platform_account_id not nullable
        if ($driver === 'sqlite') {
            // SQLite: recreate table with NOT NULL
            $tableInfo = DB::select("PRAGMA table_info(user_games)");
            
            $columns = [];
            $columnNames = [];
            foreach ($tableInfo as $col) {
                $columnNames[] = $col->name;
                if ($col->name === 'id') {
                    $columns[] = 'id INTEGER PRIMARY KEY AUTOINCREMENT';
                } elseif ($col->name === 'platform_account_id') {
                    $columns[] = 'platform_account_id INTEGER NOT NULL';
                } else {
                    $type = $col->type;
                    $notNull = $col->notnull ? 'NOT NULL' : 'NULL';
                    $default = '';
                    if ($col->dflt_value !== null) {
                        // Remove quotes if present (SQLite returns quoted strings)
                        $value = trim($col->dflt_value, "'\"");
                        if (is_numeric($value)) {
                            $default = "DEFAULT {$value}";
                        } else {
                            $default = "DEFAULT '{$value}'";
                        }
                    }
                    $columns[] = trim("{$col->name} {$type} {$notNull} {$default}");
                }
            }
            
            $columnDefs = implode(', ', $columns);
            $columnList = implode(', ', $columnNames);
            
            DB::statement("CREATE TABLE user_games_new ({$columnDefs})");
            
            // Copy data (only rows where platform_account_id is NOT NULL)
            DB::statement("INSERT INTO user_games_new ({$columnList}) SELECT {$columnList} FROM user_games WHERE platform_account_id IS NOT NULL");
            
            Schema::drop('user_games');
            DB::statement('ALTER TABLE user_games_new RENAME TO user_games');
            
            Schema::table('user_games', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('platform_account_id')->references('id')->on('platform_accounts')->onDelete('cascade');
                $table->foreign('platform_game_id')->references('id')->on('platform_games')->onDelete('cascade');
                $table->index('user_id');
                $table->index('platform_game_id');
            });
        } else {
            // MySQL/MariaDB syntax
            DB::statement('ALTER TABLE user_games MODIFY platform_account_id BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('user_games', function (Blueprint $table) {
            $table->unique(['user_id', 'platform_account_id', 'platform_game_id']);
        });
    }
};
