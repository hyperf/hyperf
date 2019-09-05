<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Cache;

use Hyperf\Di\MetadataCollector;
use Hyperf\Utils\Traits\Container;

class CacheListenerCollector extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];
}
