<?php

declare(strict_types=1);

use App\Modules\Users\Enums\UserStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('status', 32)->default(UserStatus::Active->value);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->unique('email', 'users_email_unique');
            $table->index(['status', 'created_at'], 'users_status_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
