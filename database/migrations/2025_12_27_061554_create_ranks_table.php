<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // bronze, silver, gold
            $table->string('label'); // Bronze, Silver, Gold
            $table->unsignedInteger('min_xp'); // Minimum XP required
            $table->unsignedInteger('max_xp')->nullable(); // Maximum XP (null for highest rank)
            $table->unsignedInteger('order')->default(0); // Display order
            $table->timestamps();
        });

        // Seed initial ranks
        DB::table('ranks')->insert([
            [
                'name' => 'bronze',
                'label' => 'Bronze',
                'min_xp' => 0,
                'max_xp' => 99,
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'silver',
                'label' => 'Silver',
                'min_xp' => 100,
                'max_xp' => 499,
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'gold',
                'label' => 'Gold',
                'min_xp' => 500,
                'max_xp' => null,
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};
