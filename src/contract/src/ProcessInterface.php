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
namespace Hyperf\Contract;

use Swoole\Coroutine\Server as CoServer;
use Swoole\Server;

interface ProcessInterface
{
    /**
     * Create the process object according to process number and bind to server.
     * @param CoServer|Server $server
     */
    public function bind($server): void;

    /**
     * Determine if the process should start ?
     * @param CoServer|Server $server
     */
    public function isEnable($server): bool;

    /**
     * The logical of process will place in here.
     */
    public function handle(): void;
}
