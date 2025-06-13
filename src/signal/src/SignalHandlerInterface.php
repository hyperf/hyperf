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

namespace Hyperf\Signal;

interface SignalHandlerInterface
{
    public const WORKER = 1;

    public const PROCESS = 2;

    /**
     * @return array [[ WOKKER, SIGNAL ]]
     */
    public function listen(): array;

    public function handle(int $signal): void;
}
