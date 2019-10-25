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

namespace Hyperf\Metric\Process;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Metric\Event\MetricFactoryReady;
use Hyperf\Metric\MetricFactoryPicker;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Metric Process.
 * @Process
 */
class MetricProcess extends AbstractProcess
{
    public $name = 'metric';

    public $nums = 1;

    /**
     * @var MetricFactoryInterface
     */
    protected $factory;

    public function isEnable(): bool
    {
        $config = $this->container->get(ConfigInterface::class);
        return $config->get('metric.use_standalone_process') ?? false;
    }

    public function handle(): void
    {
        $this->factory = make(
            MetricFactoryPicker::class,
            ['inMetricProcess' => true]
        )($this->container);
        $this
            ->container
            ->get(EventDispatcherInterface::class)
            ->dispatch(new MetricFactoryReady($this->factory));
        $this
            ->factory
            ->handle();
    }
}
