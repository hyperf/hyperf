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
namespace Hyperf\Retry;

class FlatStrategy implements SleepStrategyInterface
{
    /**
     * @var int
     */
    private $base;

    public function __construct(int $base)
    {
        $this->base = $base;
    }

    public function sleep(): void
    {
        usleep($this->base * 1000);
    }
}
