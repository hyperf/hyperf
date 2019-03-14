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

namespace Hyperf\Framework\Event;

class MainWorkerStart
{
    /**
     * @var \Swoole\Server
     */
    private $server;

    /**
     * @var int
     */
    private $workerId;

    public function __construct($server, int $workerId)
    {
        $this->server = $server;
        $this->workerId = $workerId;
    }
}
