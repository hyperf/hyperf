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
     * @var array|mixed|string
     */
    public $data;

    public function __construct(\Swoole\Server $server, int $fromWorkerId, $data)
    {
        $this->server = $server;
        $this->fromWorkerId = $fromWorkerId;
        $this->data = $data;
    }
}
