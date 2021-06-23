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
    public function configFetcherHandle(): void;

    public function bootProcessHandle(object $event): void;

    public function onPipeMessageHandle(object $event): void;
}
