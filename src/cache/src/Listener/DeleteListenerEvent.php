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

namespace Hyperf\Cache\Listener;

use Hyperf\Cache\CacheListenerCollector;
use Hyperf\Cache\Exception\CacheException;

class DeleteListenerEvent extends DeleteEvent
{
    public function __construct(string $listener, array $arguments)
    {
        $config = CacheListenerCollector::getListener($listener);
        if (! $config) {
            throw new CacheException(sprintf('listener %s is not defined.', $listener));
        }

        $className = $config['className'];
        $method = $config['method'];

        parent::__construct($className, $method, $arguments);
    }
}
