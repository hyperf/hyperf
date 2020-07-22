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
namespace HyperfTest\Utils\Stub;

class SerializableException extends \RuntimeException implements \Serializable
{
    /**
     * String representation of object.
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return \serialize([$this->message, $this->code, $this->file, $this->line]);
    }

    /**
     * Constructs the object.
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     */
    public function unserialize($serialized)
    {
        [$this->message, $this->code, $this->file, $this->line] = \unserialize($serialized);
    }
}
