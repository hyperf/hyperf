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
     * @param string $payload message to decode
     *
     * @return mixed
     */
    public function encode($payload)
    {
        return json_encode($payload);
    }

    /**
     * Decodes a message from JSON.
     *
     * @param string $payload message to decode
     *
     * @return mixed
     */
    public function decode($payload)
    {
        return json_decode($payload, true);
    }
}
