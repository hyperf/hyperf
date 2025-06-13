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

namespace HyperfTest\Codec\Stub;

use JsonSerializable;

class Car implements JsonSerializable
{
    public function jsonSerialize(): mixed
    {
        throw new StringCodeException('Json Serialize failed.', 'A0001');
    }
}
