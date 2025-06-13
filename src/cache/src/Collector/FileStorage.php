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

namespace Hyperf\Cache\Collector;

class FileStorage
{
    protected ?int $expiredTime = null;

    public function __construct(protected mixed $data, $ttl)
    {
        if (is_numeric($ttl) && $ttl > 0) {
            $this->expiredTime = time() + $ttl;
        }
    }

    public function getData()
    {
        return $this->data;
    }

    public function isExpired(): bool
    {
        if (is_null($this->expiredTime)) {
            return false;
        }

        return time() > $this->expiredTime;
    }
}
