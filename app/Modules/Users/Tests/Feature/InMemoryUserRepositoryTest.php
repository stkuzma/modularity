<?php

declare(strict_types=1);

namespace App\Modules\Users\Tests\Feature;

use App\Modules\Users\Contracts\UserRepository;
use App\Modules\Users\Tests\Contracts\UserRepositoryContract;
use App\Modules\Users\Tests\Support\InMemoryUserRepository;

final class InMemoryUserRepositoryTest extends UserRepositoryContract
{
    private ?InMemoryUserRepository $repository = null;

    protected function repository(): UserRepository
    {
        return $this->repository ??= new InMemoryUserRepository;
    }
}
