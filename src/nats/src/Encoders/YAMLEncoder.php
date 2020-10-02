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
 * Class YAMLEncoder.
 *
 * Encodes and decodes messages in YAML format.
 */
class YAMLEncoder implements Encoder
{
    /**
     * Encodes a message to YAML.
     *
     * @param string $payload message to decode
     *
     * @return mixed
     */
    public function encode($payload)
    {
        return yaml_emit($payload);
    }

    /**
     * Decodes a message from YAML.
     *
     * @param string $payload message to decode
     *
     * @return mixed
     */
    public function decode($payload)
    {
        return yaml_parse($payload);
    }
}
