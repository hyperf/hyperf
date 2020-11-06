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

class OnWorkerError
{
    /**
     * @var \Swoole\Server
     */
    public $server;

    /**
     * @var int
     */
    public $workerId;

    /**
     * @var int
     */
    public $workerPid;

    /**
     * @var int
     */
    public $exitCode;

    /**
     * @var int
     */
    public $signal;

    public function __construct($server, int $workerId, int $workerPid, int $exitCode, int $signal)
    {
        $this->server = $server;
        $this->workerId = $workerId;
        $this->workerPid = $workerPid;
        $this->exitCode = $exitCode;
        $this->signal = $signal;
    }
}
