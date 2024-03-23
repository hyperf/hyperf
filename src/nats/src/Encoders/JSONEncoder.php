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
 * Class JSONEncoder.
 *
 * Encodes and decodes messages in JSON format.
 */
class JSONEncoder implements Encoder
{
    /**
     * Encodes a message to JSON.
     *
     * @param mixed $payload message to decode
     */
    public function encode(mixed $payload): string
    {
        return json_encode($payload);
    }

    /**
     * Decodes a message from JSON.
     *
     * @param string $payload message to decode
     */
    public function decode(string $payload): mixed
    {
        return json_decode($payload, true);
    }
}
