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
namespace Hyperf\Utils\Exception;

class_alias(\Hyperf\Coroutine\Exception\ParallelExecutionException::class, ParallelExecutionException::class);

if (! class_exists(ParallelExecutionException::class)) {
    /**
     * @deprecated since 3.1, use Hyperf\Coroutine\Exception\ParallelExecutionException instead.
     */
    class ParallelExecutionException extends \Hyperf\Coroutine\Exception\ParallelExecutionException
    {
    }
}
