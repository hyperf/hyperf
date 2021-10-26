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
namespace Hyperf\ReactiveX;

use Hyperf\ReactiveX\Contract\BroadcasterInterface;

class AllProcessesBroadcaster implements BroadcasterInterface
{
    /**
     * @var ServerBroadcaster
     */
    protected $serverBroadcaster;

    /**
     * @var UserProcessesBroadcaster
     */
    protected $userProcessesBroadcaster;

    public function __construct(
        ServerBroadcaster $serverBroadcaster,
        UserProcessesBroadcaster $userProcessesBroadcaster
    ) {
        $this->serverBroadcaster = $serverBroadcaster;
        $this->userProcessesBroadcaster = $userProcessesBroadcaster;
    }

    public function broadcast(IpcMessageWrapper $message)
    {
        $this->serverBroadcaster->broadcast($message);
        $this->userProcessesBroadcaster->broadcast($message);
    }
}
