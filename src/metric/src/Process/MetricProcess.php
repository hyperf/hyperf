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
namespace Hyperf\Metric\Process;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Metric\Event\MetricFactoryReady;
use Hyperf\Metric\MetricFactoryPicker;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Server;

/**
 * Metric Process.
 */
class MetricProcess extends AbstractProcess
{
    public string $name = 'metric';

    public int $nums = 1;

    protected MetricFactoryInterface $factory;

    public function isEnable($server): bool
    {
        $config = $this->container->get(ConfigInterface::class);
        return $server instanceof Server && $config->get('metric.use_standalone_process', true);
    }

    public function handle(): void
    {
        MetricFactoryPicker::$inMetricProcess = true;
        $this->factory = make(MetricFactoryInterface::class);
        $this
            ->container
            ->get(EventDispatcherInterface::class)
            ->dispatch(new MetricFactoryReady($this->factory));
        $this
            ->factory
            ->handle();
    }
}
