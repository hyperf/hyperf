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
namespace Zipkin\Propagation;

use ArrayAccess;
use Zipkin\Propagation\Exceptions\InvalidPropagationCarrier;
use Zipkin\Propagation\Exceptions\InvalidPropagationKey;

use function is_array;

class Map implements Getter, Setter
{
    /**
     * {@inheritdoc}
     *
     * If the carrier is an array, the lookup is case insensitive, this is
     * mainly because Map getter is commonly used for those cases where the
     * HTTP framework does not follow the PSR request/response objects (e.g.
     * Symfony) and thus the header bag should be treated as a map. ArrayAccess
     * can't be case insensitive because we can not know the keys on beforehand.
     *
     * @param array|ArrayAccess $carrier
     */
    public function get($carrier, string $key): ?string
    {
        $lKey = \strtolower($key);

        if ($carrier instanceof ArrayAccess) {
            return $carrier->offsetExists($lKey) ? $carrier->offsetGet($lKey) : null;
        }

        if (is_array($carrier)) {
            if (empty($carrier)) {
                return null;
            }

            foreach ($carrier as $k => $value) {
                if (strtolower((string) $k) === $lKey) {
                    return $value;
                }
            }

            return null;
        }

        throw InvalidPropagationCarrier::forCarrier($carrier);
    }

    /**
     * {@inheritdoc}
     * @param array|ArrayAccess $carrier
     */
    public function put(&$carrier, string $key, string $value): void
    {
        if ($key === '') {
            throw InvalidPropagationKey::forEmptyKey();
        }

        // Lowercasing the key was a first attempt to be compatible with the
        // getter when using the Map getter for HTTP headers.
        $lKey = \strtolower($key);

        if ($carrier instanceof ArrayAccess || is_array($carrier)) {
            $carrier[$lKey] = $value;
            return;
        }

        throw InvalidPropagationCarrier::forCarrier($carrier);
    }
}
