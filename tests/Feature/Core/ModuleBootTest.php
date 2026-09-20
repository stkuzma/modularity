<?php

declare(strict_types=1);

namespace Tests\Feature\Core;

use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Modules\Demo\Contracts\Greeter;
use Tests\Fixtures\Modules\Demo\Providers\DemoServiceProvider;
use Tests\Fixtures\Modules\Demo\Services\PlainGreeter;
use Tests\TestCase;

final class ModuleBootTest extends TestCase
{
    #[Test]
    public function it_binds_what_the_module_declares(): void
    {
        $this->assertInstanceOf(PlainGreeter::class, $this->app->make(Greeter::class));
    }

    #[Test]
    public function it_loads_the_module_api_routes_under_the_api_prefix(): void
    {
        $this->getJson('/api/demo/greet/Ada')
            ->assertOk()
            ->assertJson(['message' => 'Hello, Ada.']);
    }

    #[Test]
    public function it_names_the_module_routes(): void
    {
        $this->assertTrue($this->app['router']->has('demo.greet'));
    }

    #[Test]
    public function it_merges_the_module_config_under_the_module_key(): void
    {
        $this->assertSame('Hello', config('demo.salutation'));
    }

    #[Test]
    public function it_resolves_the_module_name_and_path_from_the_provider(): void
    {
        $provider = new DemoServiceProvider($this->app);

        $this->assertSame('Demo', $provider->moduleName());
        $this->assertDirectoryExists($provider->modulePath());
        $this->assertFileExists($provider->modulePath().'/routes/api.php');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->register(DemoServiceProvider::class);
    }
}
