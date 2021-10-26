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

class OnPacket
{
    /**
     * @var \Swoole\Server
     */
    public $server;

    /**
     * @var string
     */
    public $data;

    /**
     * @var array
     */
    public $clientInfo;

    public function __construct($server, string $data, array $clientInfo)
    {
        $this->server = $server;
        $this->data = $data;
        $this->clientInfo = $clientInfo;
    }
}
