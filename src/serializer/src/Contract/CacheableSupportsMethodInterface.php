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
    /**
     * @deprecated since v3.1, will be removed in v3.2, implement "getSupportedTypes(?string $format)" instead
     */
    interface CacheableSupportsMethodInterface extends SymfonyCacheableSupportsMethodInterface
    {
    }
} else {
    /**
     * @deprecated since v3.1, will be removed in v3.2, implement "getSupportedTypes(?string $format)" instead
     */
    interface CacheableSupportsMethodInterface
    {
    }
}
