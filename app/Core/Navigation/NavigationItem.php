<?php

declare(strict_types=1);

namespace App\Core\Navigation;

final readonly class NavigationItem
{
    public function __construct(
        public string $label,
        public string $route,
        public ?string $permission = null,
        public int $order = 100,
    ) {}
}
