<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 128);
            $table->string('token_hash', 64);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique('token_hash', 'auth_tokens_token_hash_unique');
            $table->index(['user_id', 'expires_at'], 'auth_tokens_user_expiry_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_tokens');
    }
};
