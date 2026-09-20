<?php

declare(strict_types=1);

namespace App\Core\Pipeline;

/**
 * One use case. Knows nothing about HTTP or presentation.
 *
 * @template TInput
 * @template TOutput
 */
interface Processor
{
    /**
     * @param  TInput  $input
     * @return TOutput
     */
    public function process(mixed $input): mixed;
}
