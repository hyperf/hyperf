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

namespace Hyperf\Cache\Listener;

use Hyperf\Cache\CacheListenerCollector;
use Hyperf\Cache\Exception\CacheException;

class DeleteListenerEvent extends DeleteEvent
{
    public function __construct(string $listener, array $arguments)
    {
        $config = CacheListenerCollector::get($listener, null);
        if (! $config) {
            throw new CacheException(sprintf('listener %s is not defined.', $listener));
        }

        $className = $config['className'];
        $method = $config['method'];

        parent::__construct($className, $method, $arguments);
    }
}
