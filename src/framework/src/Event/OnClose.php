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

class OnClose
{
    /**
     * @var \Swoole\Server
     */
    public $server;

    /**
     * @var int
     */
    public $fd;

    /**
     * @var int
     */
    public $reactorId;

    public function __construct($server, int $fd, int $reactorId)
    {
        $this->server = $server;
        $this->fd = $fd;
        $this->reactorId = $reactorId;
    }
}
