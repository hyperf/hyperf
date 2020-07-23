<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Paginator;

use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Contract\PaginatorInterface;
use Hyperf\Paginator\Listener\PageResolverListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                PaginatorInterface::class => Paginator::class,
                LengthAwarePaginatorInterface::class => LengthAwarePaginator::class,
            ],
            'listeners' => [
                PageResolverListener::class,
            ],
        ];
    }
}
