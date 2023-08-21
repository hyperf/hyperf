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
namespace Hyperf\Di;

use Hyperf\Context\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use TypeError;

/**
 * @return ContainerInterface
 * @throws TypeError
 */
function di()
{
    return ApplicationContext::getContainer();
}

/**
 * @template T
 * @param class-string<T> $id
 * @return T
 * @throws NotFoundExceptionInterface
 * @throws ContainerExceptionInterface
 */
function get(string $id)
{
    return di()->get($id);
}
