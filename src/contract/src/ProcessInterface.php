<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Contract;

use Swoole\Server;

interface ProcessInterface
{
    /**
     * Create the process object according to process number and bind to server.
     */
    public function bind(Server $server): void;

    /**
     * Determine if the process should start ?
     */
    public function isEnable(): bool;

    /**
     * The logical of process will place in here.
     */
    public function handle(): void;
}
