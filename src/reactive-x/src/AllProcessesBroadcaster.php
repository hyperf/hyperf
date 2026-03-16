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
    public function __construct(
        protected ServerBroadcaster $serverBroadcaster,
        protected UserProcessesBroadcaster $userProcessesBroadcaster
    ) {
    }

    public function broadcast(IpcMessageWrapper $message): void
    {
        $this->serverBroadcaster->broadcast($message);
        $this->userProcessesBroadcaster->broadcast($message);
    }
}
