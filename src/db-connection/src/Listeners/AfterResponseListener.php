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

namespace Hyperf\DbConnection\Listeners;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Event\AfterResponse;
use Hyperf\Utils\Context;

/**
 * @Listener
 */
class AfterResponseListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            AfterResponse::class
        ];
    }

    public function process(object $event)
    {
        echo 'afterResponse' . PHP_EOL;

        if (Context::has('databases')) {
            $dbs = Context::get('databases');
            foreach ($dbs as $conn) {
                if ($conn instanceof ConnectionInterface) {
                    $conn->release();
                }
            }
        }
    }
}
