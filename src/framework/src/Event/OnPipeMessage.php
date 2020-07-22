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

use Swoole\Server;

class OnPipeMessage
{
    /**
     * @var \Swoole\Server
     */
    public $server;

    /**
     * @var int
     */
    public $fromWorkerId;

    /**
     * @var mixed
     */
    public $data;

    public function __construct(Server $server, int $fromWorkerId, $data)
    {
        $this->server = $server;
        $this->fromWorkerId = $fromWorkerId;
        $this->data = $data;
    }
}
