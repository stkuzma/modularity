<?php

declare(strict_types=1);

namespace Tests\Fixtures\Modules\Demo\Providers;

use App\Core\Module\ModuleServiceProvider;
use Tests\Fixtures\Modules\Demo\Contracts\Greeter;
use Tests\Fixtures\Modules\Demo\Services\PlainGreeter;

final class DemoServiceProvider extends ModuleServiceProvider
{
    protected function bindings(): array
    {
        return [
            Greeter::class => PlainGreeter::class,
        ];
    }
}
