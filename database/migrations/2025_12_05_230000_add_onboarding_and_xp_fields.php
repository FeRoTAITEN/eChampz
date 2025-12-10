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
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('role');
            $table->enum('represent_type', ['organization', 'freelancer'])->nullable()->after('date_of_birth');
            $table->string('organization_name')->nullable()->after('represent_type');
            $table->string('position')->nullable()->after('organization_name');
            $table->timestamp('onboarding_completed_at')->nullable()->after('position');
            $table->unsignedInteger('xp_total')->default(0)->after('onboarding_completed_at');
        });

        Schema::create('xp_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source');
            $table->string('source_id')->default('');
            $table->integer('amount');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'source', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth',
                'represent_type',
                'organization_name',
                'position',
                'onboarding_completed_at',
                'xp_total',
            ]);
        });

        Schema::dropIfExists('xp_transactions');
    }
};
