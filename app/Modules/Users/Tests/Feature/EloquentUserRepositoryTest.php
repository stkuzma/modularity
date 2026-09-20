<?php

declare(strict_types=1);

namespace App\Modules\Users\Tests\Feature;

use App\Modules\Users\Contracts\UserRepository;
use App\Modules\Users\Repositories\EloquentUserRepository;
use App\Modules\Users\Tests\Contracts\UserRepositoryContract;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class EloquentUserRepositoryTest extends UserRepositoryContract
{
    use RefreshDatabase;

    protected function repository(): UserRepository
    {
        return new EloquentUserRepository;
    }
}
