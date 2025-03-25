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

use Hyperf\Engine\Channel;
use Hyperf\SingleFlight\Exception\RuntimeException;
use Hyperf\Support\Traits\Container;
use Throwable;

use function Hyperf\Support\call;

class Barrier
{
    use Container;

    /**
     * @throws Throwable
     */
    public static function yield(string $barrierKey, callable $processor): mixed
    {
        if (! self::has($barrierKey)) {
            $chan = new Channel(1);
            self::set($barrierKey, $chan);
            try {
                $ret = call($processor);
                while ($chan->stats()['consumer_num'] > 0) {
                    $chan->push($ret);
                }
                return $ret;
            } catch (Throwable $throwable) {
                while ($chan->stats()['consumer_num'] > 0) {
                    $chan->push($throwable);
                }
                throw $throwable;
            } finally {
                unset(self::$container[$barrierKey]);
            }
        }

        $ret = self::get($barrierKey)->pop();
        if ($ret instanceof Throwable) {
            throw new RuntimeException(previous: $ret);
        }
        return $ret;
    }
}
