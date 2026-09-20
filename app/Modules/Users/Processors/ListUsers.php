<?php

declare(strict_types=1);

namespace App\Modules\Users\Processors;

use App\Core\Pipeline\Processor;
use App\Modules\Users\Contracts\UserRepository;
use App\Modules\Users\Data\UserFilter;
use App\Modules\Users\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @implements Processor<UserFilter, LengthAwarePaginator<int, User>>
 */
final readonly class ListUsers implements Processor
{
    public function __construct(private UserRepository $users) {}

    public function process(mixed $input): LengthAwarePaginator
    {
        return $this->users->paginate($input);
    }
}
