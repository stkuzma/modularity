<?php

declare(strict_types=1);

namespace App\Core\Pipeline;

/**
 * Shapes a domain object for output. Fields are listed, never reflected.
 *
 * @template TSubject
 */
interface Presenter
{
    /**
     * @param  TSubject  $subject
     * @return array<string, mixed>
     */
    public function present(mixed $subject): array;
}
