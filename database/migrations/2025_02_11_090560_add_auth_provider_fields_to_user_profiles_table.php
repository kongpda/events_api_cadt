<?php

declare(strict_types=1);

use App\Enums\AuthProvider;
use App\Enums\UserStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table): void {
            // Add new columns after existing ones
            $table->string('auth_provider')->default(AuthProvider::EMAIL->value)->after('avatar');
            $table->string('auth_provider_id')->nullable()->after('auth_provider');

            // Update status field to use enum if it doesn't have a default
            $table->string('status')->default(UserStatus::ACTIVE->value)->change();
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'auth_provider',
                'auth_provider_id',
            ]);
        });
    }
};
