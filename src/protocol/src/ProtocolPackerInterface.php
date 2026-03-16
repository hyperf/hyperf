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

namespace Hyperf\Protocol;

use Hyperf\Contract\PackerInterface;

interface ProtocolPackerInterface extends PackerInterface
{
    public const HEAD_LENGTH = 4;

    public function length(string $head): int;
}
