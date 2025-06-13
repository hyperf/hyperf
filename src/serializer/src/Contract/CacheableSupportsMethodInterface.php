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

namespace Hyperf\Serializer\Contract;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface as SymfonyCacheableSupportsMethodInterface;

if (interface_exists(SymfonyCacheableSupportsMethodInterface::class)) {
    interface CacheableSupportsMethodInterface extends SymfonyCacheableSupportsMethodInterface
    {
    }
} else {
    interface CacheableSupportsMethodInterface
    {
    }
}
