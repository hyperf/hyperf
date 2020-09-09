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
namespace Hyperf\AsyncQueue;

use Hyperf\Contract\CompressInterface;
use Hyperf\Contract\UnCompressInterface;

abstract class Job implements JobInterface, CompressInterface, UnCompressInterface
{
    /**
     * @var int
     */
    protected $maxAttempts = 0;

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    /**
     * @return static
     */
    public function uncompress(): CompressInterface
    {
        foreach ($this as $key => $value) {
            if ($value instanceof UnCompressInterface) {
                $this->{$key} = $value->uncompress();
            }
        }

        return $this;
    }

    /**
     * @return static
     */
    public function compress(): UnCompressInterface
    {
        foreach ($this as $key => $value) {
            if ($value instanceof CompressInterface) {
                $this->{$key} = $value->compress();
            }
        }

        return $this;
    }
}
