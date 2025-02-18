<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('address');
            $table->string('feature_image')->nullable();
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignUlid('user_id')->constrained('users');
            $table->foreignUlid('organizer_id')->constrained('organizers');
            $table->string('participation_type');
            $table->integer('capacity')->comment('0 for unlimited');
            $table->timestamp('registration_deadline');
            $table->string('registration_status');
            $table->string('event_type');
            $table->string('online_url')->nullable()->comment('Required if location_type is online or hybrid');
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
