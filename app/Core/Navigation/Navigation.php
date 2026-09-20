<?php

declare(strict_types=1);

namespace App\Core\Navigation;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Routing\Router;

final class Navigation
{
    /** @var list<NavigationItem> */
    private array $items = [];

    public function __construct(
        private readonly Router $router,
        private readonly Gate $gate,
    ) {}

    /**
     * @param  list<NavigationItem>  $items
     */
    public function add(array $items): void
    {
        foreach ($items as $item) {
            $this->items[] = $item;
        }
    }

    /**
     * @return list<NavigationItem>
     */
    public function visibleTo(?Authenticatable $actor): array
    {
        $visible = array_values(array_filter(
            $this->items,
            fn (NavigationItem $item): bool => $this->router->has($item->route)
                && ($item->permission === null
                    || ($actor !== null && $this->gate->forUser($actor)->allows($item->permission))),
        ));

        usort($visible, static fn (NavigationItem $a, NavigationItem $b): int => [$a->order, $a->label] <=> [$b->order, $b->label]);

        return $visible;
    }
}
