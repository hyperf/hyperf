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
namespace Hyperf\ServiceGovernance;

use Hyperf\ServiceGovernance\Exception\IPReadFailedException;

class IPReader implements IPReaderInterface
{
    public function read(): string
    {
        try {
            return get_local_ip();
        } catch (\Throwable $throwable) {
            throw new IPReadFailedException($throwable->getMessage());
        }
    }
}
