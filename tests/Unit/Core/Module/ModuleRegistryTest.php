<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Module;

use App\Core\Module\ModuleException;
use App\Core\Module\ModuleRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Tests\Fixtures\Modules\Demo\Providers\DemoServiceProvider;

final class ModuleRegistryTest extends TestCase
{
    #[Test]
    public function it_accepts_a_module_provider(): void
    {
        $registry = new ModuleRegistry([DemoServiceProvider::class]);

        $this->assertSame([DemoServiceProvider::class], $registry->providers());
    }

    #[Test]
    public function it_derives_the_module_name_from_the_provider(): void
    {
        $registry = new ModuleRegistry([DemoServiceProvider::class]);

        $this->assertSame(['Demo'], $registry->names());
    }

    #[Test]
    public function it_rejects_a_class_that_is_not_a_module_provider(): void
    {
        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage('does not extend');

        /** @phpstan-ignore argument.type */
        new ModuleRegistry([stdClass::class]);
    }

    #[Test]
    public function it_is_empty_by_default(): void
    {
        $this->assertSame([], (new ModuleRegistry([]))->providers());
    }
}
