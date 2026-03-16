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

namespace Hyperf\LoadBalancer;

class Node
{
    public ?string $schema = null;

    public function __construct(public string $host, public int $port, public int $weight = 0, public string $pathPrefix = '')
    {
    }
}
