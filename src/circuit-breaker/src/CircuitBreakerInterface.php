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

namespace Hyperf\CircuitBreaker;

interface CircuitBreakerInterface
{
    public function state(): State;

    public function attempt(): bool;

    public function open(): void;

    public function close(): void;

    public function halfOpen(): void;

    public function getDuration(): float;

    public function getFailCounter(): int;

    public function getSuccessCounter(): int;

    public function incrSuccessCounter(): int;

    public function incrFailCounter(): int;
}
