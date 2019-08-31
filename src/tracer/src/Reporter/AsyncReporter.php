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

namespace Hyperf\Tracer\Reporter;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Container;
use Hyperf\Process\ProcessCollector;
use Hyperf\Tracer\ReporterMessage;
use Psr\Container\ContainerInterface;
use Swoole\Process;
use Zipkin\Recording\Span as MutableSpan;
use Zipkin\Reporter;

class AsyncReporter implements Reporter
{
    protected const TARGET_PROCESS_NAME = 'tracer-reporter';

    /**
     * @var Container
     * @Inject
     */
    protected $container;

    /**
     * @var array
     */
    private $options;

    public function __construct(
        ContainerInterface $container,
        array $options
    ) {
        $this->options = $options;
        $this->container = $container;
    }

    /**
     * @param MutableSpan[] $spans
     */
    public function report(array $spans): void
    {
        $logger = $this->container->get(StdoutLoggerInterface::class);
        $processes = ProcessCollector::get(static::TARGET_PROCESS_NAME);
        if (empty($processes)) {
            $logger->warning('no target process started.');
            return;
        }

        $index = array_rand($processes);
        /** @var Process $process */
        $process = $processes[$index];
        $logger->debug(sprintf('deliver to %d', $index));
        $process->exportSocket()->send(serialize(new ReporterMessage(
            $this->options,
            $spans
        )));
    }
}
