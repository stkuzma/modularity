<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table): void {
            $table->id();
            $table->string('email');
            $table->string('token_hash', 64);
            $table->unsignedBigInteger('invited_by')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->unique('token_hash', 'invitations_token_hash_unique');
            $table->index(['email', 'accepted_at'], 'invitations_email_accepted_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
