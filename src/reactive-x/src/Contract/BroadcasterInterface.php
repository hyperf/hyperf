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

namespace Hyperf\ReactiveX\Contract;

use Hyperf\ReactiveX\IpcMessageWrapper;

interface BroadcasterInterface
{
    public function broadcast(IpcMessageWrapper $message): void;
}
