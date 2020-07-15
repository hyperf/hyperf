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
namespace Hyperf\ModelCache\Handler;

use Hyperf\ModelCache\Config;
use Psr\SimpleCache\CacheInterface;

interface HandlerInterface extends CacheInterface
{
    public function getConfig(): Config;

    public function incr($key, $column, $amount): bool;
}
