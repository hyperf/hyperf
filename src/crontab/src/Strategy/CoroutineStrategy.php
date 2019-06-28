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

namespace Hyperf\Crontab\Strategy;

use Carbon\Carbon;
use Hyperf\Crontab\Crontab;

class CoroutineStrategy extends AbstractStrategy
{
    public function dispatch(Crontab $crontab)
    {
        go(function () use ($crontab) {
            if ($crontab->getExecuteTime() instanceof Carbon) {
                $wait = $crontab->getExecuteTime()->getTimeStamp() - time();
                $wait > 0 && \Swoole\Coroutine::sleep($wait);
                $content = date('Y-m-d H:i:s', time()) . ' ' . $crontab->getName();
                var_dump($content);
                file_put_contents(BASE_PATH . '/runtime/crontabs', $content . PHP_EOL, FILE_APPEND);
            }
        });
    }
}
