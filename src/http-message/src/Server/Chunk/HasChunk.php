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

namespace Hyperf\HttpMessage\Server\Chunk;

use Hyperf\Engine\Contract\Http\Writable;

trait HasChunk
{
    public function write(string $content): bool
    {
        if (isset($this->connection) && $this->connection instanceof Writable) {
            return $this->connection->write($content);
        }

        return false;
    }
}
