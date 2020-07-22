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
namespace Hyperf\Rpc\IdGenerator;

use Hyperf\Contract\IdGeneratorInterface;

class RequestIdGenerator implements IdGeneratorInterface
{
    public function generate(): string
    {
        $us = strstr(microtime(), ' ', true);
        return strval($us * 1000 * 1000) . rand(100, 999);
    }
}
