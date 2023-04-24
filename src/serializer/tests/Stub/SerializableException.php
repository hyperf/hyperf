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
namespace HyperfTest\Serializer\Stub;

use RuntimeException;

class SerializableException extends RuntimeException
{
    public function __unserialize(array $data): void
    {
        [$this->message, $this->code, $this->file, $this->line] = $data;
    }

    public function __serialize(): array
    {
        return [$this->message, $this->code, $this->file, $this->line];
    }
}
