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

namespace Hyperf\Nats;

class RandomGenerator
{
    /**
     * A simple wrapper on random_bytes.
     *
     * @param int $len length of the string
     *
     * @return string random string
     */
    public function generateString($len): string
    {
        return bin2hex(random_bytes($len));
    }
}
