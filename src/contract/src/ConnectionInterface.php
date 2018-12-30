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

namespace Hyperf\Contract;

interface ConnectionInterface
{
    public function getConnection();

    public function reconnect(): bool;

    public function check(): bool;

    public function close(): bool;

    public function release(): void;
}
