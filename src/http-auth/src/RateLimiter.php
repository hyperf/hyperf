<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpAuth;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\InteractsWithTime;

class RateLimiter
{
    use InteractsWithTime;

    /**
     * @Inject
     * @var \Psr\SimpleCache\CacheInterface
     */
    protected $cache;

    public function tooManyAttempts($key, $maxAttempts)
    {
        if ($this->attempts($key) >= $maxAttempts) {
            if ($this->cache->has($key . ':timer')) {
                return true;
            }

            $this->resetAttempts($key);
        }

        return false;
    }

    public function hit($key, $decaySeconds = 60)
    {
        $this->cache->set(
            $key . ':timer',
            $this->availableAt($decaySeconds),
            $decaySeconds
        );

        $added = $this->cache->set($key, 0, $decaySeconds);

        $hits = (int) $this->cache->get($key);

        if (! $added && $hits == 1) {
            $this->cache->set($key, 1, $decaySeconds);
        }

        return $hits;
    }

    public function attempts($key)
    {
        return $this->cache->get($key, 0);
    }

    public function resetAttempts($key)
    {
        return $this->cache->delete($key);
    }

    public function retriesLeft($key, $maxAttempts)
    {
        $attempts = $this->attempts($key);

        return $maxAttempts - $attempts;
    }

    public function clear($key)
    {
        $this->resetAttempts($key);

        $this->cache->delete($key . ':timer');
    }

    public function availableIn($key)
    {
        return $this->cache->get($key . ':timer') - $this->currentTime();
    }
}
