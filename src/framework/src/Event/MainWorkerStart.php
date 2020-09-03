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
namespace Hyperf\Framework\Event;

class MainWorkerStart
{
    /**
     * @var \Swoole\Server
     */
    public $server;

    /**
     * @var int
     */
    public $workerId;

    public function __construct($server, int $workerId)
    {
        $this->server = $server;
        $this->workerId = $workerId;
    }
}
