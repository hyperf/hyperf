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

namespace Hyperf\Metric\Adapter\RemoteProxy;

use Hyperf\Metric\Contract\MetricCollectorInterface;
use Hyperf\Process\ProcessCollector;

class MetricCollector implements MetricCollectorInterface
{
    protected const TARGET_PROCESS_NAME = 'metric';

    protected array $buffer = [];

    public function __construct(
        protected int $bufferSize = 200
    ) {
    }

    public function add(object $data): void
    {
        $this->buffer[] = $data;

        if (count($this->buffer) >= $this->bufferSize) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        $process = ProcessCollector::get(static::TARGET_PROCESS_NAME)[0];
        $buffer = $this->buffer;
        $this->buffer = [];
        $process->write(serialize($buffer));
    }

    public function getBuffer(): array
    {
        return $this->buffer;
    }
}
