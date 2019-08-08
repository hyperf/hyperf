<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\AsyncQueue;

use Hyperf\Contract\CodeDegenerateInterface;
use Hyperf\Contract\CodeGenerateInterface;

abstract class Job implements JobInterface, CodeGenerateInterface, CodeDegenerateInterface
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
     * @return JobInterface
     */
    public function degenerate(): CodeGenerateInterface
    {
        foreach ($this as $key) {
        }
    }

    /**
     * @return JobInterface
     */
    public function generate(): CodeDegenerateInterface
    {
    }
}
