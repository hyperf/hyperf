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

namespace Hyperf\Nats\Encoders;

/**
 * Interface Encoder.
 */
interface Encoder
{
    /**
     * Encodes a message.
     *
     * @param string $payload message to decode
     */
    public function encode(mixed $payload): string;

    /**
     * Decodes a message.
     *
     * @param string $payload message to decode
     */
    public function decode(string $payload): mixed;
}
