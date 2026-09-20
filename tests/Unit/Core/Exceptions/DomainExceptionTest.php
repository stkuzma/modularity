<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Exceptions;

use App\Core\Exceptions\ConflictException;
use App\Core\Exceptions\ForbiddenException;
use App\Core\Exceptions\NotFoundException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DomainExceptionTest extends TestCase
{
    #[Test]
    public function not_found_carries_a_stable_code_and_status(): void
    {
        $e = new NotFoundException;

        $this->assertSame('core.not_found', $e->errorCode());
        $this->assertSame(404, $e->statusCode());
        $this->assertSame([], $e->details());
    }

    #[Test]
    public function the_resource_factory_namespaces_the_code(): void
    {
        $e = NotFoundException::resource('users', 42);

        $this->assertSame('users.not_found', $e->errorCode());
        $this->assertSame(['id' => 42], $e->details());
    }

    #[Test]
    public function forbidden_and_conflict_map_to_their_statuses(): void
    {
        $this->assertSame(403, (new ForbiddenException)->statusCode());
        $this->assertSame(409, (new ConflictException)->statusCode());
    }
}
