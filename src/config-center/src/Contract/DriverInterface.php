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
namespace Hyperf\ConfigCenter\Contract;

interface DriverInterface
{
    public function fetchConfig();

    public function createConfigUpdaterLoop(string $mode): void;

    public function createConfigFetcherLoop(): void;

    public function onPipeMessage(PipeMessageInterface $pipeMessage): void;
}
