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

namespace Hyperf\SingleFlight;

use Hyperf\SingleFlight\Exception\ForgetException;
use Hyperf\SingleFlight\Exception\SingleFlightException;
use Hyperf\Support\Traits\Container;
use Throwable;

class SingleFlight
{
    use Container;

    /**
     * @throws SingleFlightException|Throwable
     */
    public static function do(string $barrierKey, callable $processor, float $timeout = -1): mixed
    {
        if (! self::has($barrierKey) || self::get($barrierKey)?->isForgotten()) {
            $caller = new Caller($barrierKey);
            self::set($barrierKey, $caller);
            try {
                return $caller->share($processor);
            } finally {
                if (self::get($barrierKey)?->waiters() === 0) {
                    unset(self::$container[$barrierKey]);
                }
            }
        }

        /** @var Caller $caller */
        $caller = self::get($barrierKey);

        try {
            $ret = $caller->wait($timeout);
            if ($ret instanceof SingleFlightException) {
                throw $ret;
            }
            return $ret;
        } catch (SingleFlightException $exception) {
            if ($exception instanceof ForgetException) {
                return self::do($barrierKey, $processor, $timeout);
            }
            throw $exception;
        }
    }

    public static function forget(string $barrierKey): void
    {
        self::get($barrierKey)?->forget();
    }
}
