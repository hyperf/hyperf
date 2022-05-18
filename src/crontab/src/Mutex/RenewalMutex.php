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
namespace Hyperf\Crontab\Mutex;

use Closure;
use Hyperf\Crontab\Crontab;
use Hyperf\Redis\RedisProxy;
use Hyperf\Utils\Coroutine;

trait RenewalMutex
{
    /**
     * Mutex renews in less than 2 seconds.
     */
    public function renewalClosure(Crontab $crontab, RedisProxy $redis): Closure
    {
        return function () use ($crontab, $redis) {
            while (true) {
                $ttl = $redis->ttl($this->getMutexName($crontab));
                // expire or never expire
                if ($ttl === -2 || $ttl === -1) {
                    break;
                }
                if ($ttl < 2) {
                    $redis->set(
                        $this->getMutexName($crontab),
                        $crontab->getName(),
                        ['EX' => $crontab->getMutexExpires()]
                    );
                }
                Coroutine::sleep(1);
            }
        };
    }

    protected function getMutexName(Crontab $crontab)
    {
        return 'framework' . DIRECTORY_SEPARATOR . 'crontab-' . sha1($crontab->getName() . $crontab->getRule());
    }
}
