<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DbConnection\Cache\Handler;

use Hyperf\DbConnection\Cache\Config;
use Psr\SimpleCache\CacheInterface;

interface HandlerInterface extends CacheInterface
{
    public function getConfig(): Config;

    public function incr($key, $column, $amount): bool;
}
