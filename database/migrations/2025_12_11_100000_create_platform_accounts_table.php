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
        Schema::create('platform_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', ['playstation', 'xbox', 'steam', 'epic'])->default('playstation');
            $table->string('platform_username'); // PSN username (onlineId)
            $table->string('platform_account_id')->nullable(); // PSN account ID
            $table->text('access_token')->nullable(); // Encrypted
            $table->text('refresh_token')->nullable(); // Encrypted
            $table->timestamp('token_expires_at')->nullable();
            $table->string('npsso_token')->nullable(); // NPSSO token for PSN
            $table->boolean('is_verified')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'platform']);
            $table->index('platform_username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_accounts');
    }
};
