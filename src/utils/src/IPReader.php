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
namespace Hyperf\Utils;

use Hyperf\Contract\IPReaderInterface;
use Hyperf\Utils\Exception\IPReadFailedException;
use Throwable;

class IPReader implements IPReaderInterface
{
    public function read(): string
    {
        try {
            return Network::ip();
        } catch (Throwable $throwable) {
            throw new IPReadFailedException($throwable->getMessage());
        }
    }
}
