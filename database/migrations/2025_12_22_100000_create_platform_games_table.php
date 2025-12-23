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
        Schema::create('platform_games', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['playstation', 'xbox', 'steam', 'epic'])->default('playstation');
            $table->string('platform_game_id'); // NPWR09412_00, Xbox Title ID, Steam App ID, etc.
            $table->string('name'); // Game name
            $table->string('icon_url')->nullable();
            $table->json('metadata')->nullable(); // General game metadata
            $table->timestamps();
            
            // Unique constraint: same game ID can only exist once per platform
            $table->unique(['platform', 'platform_game_id']);
            
            // Indexes for performance
            $table->index('platform_game_id');
            $table->index('platform');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_games');
    }
};
