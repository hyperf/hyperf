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

namespace Hyperf\Metric\Event;

use Hyperf\Metric\Contract\MetricFactoryInterface;

class MetricFactoryReady
{
    /**
     * @param MetricFactoryInterface $factory a ready to use factory
     */
    public function __construct(public MetricFactoryInterface $factory)
    {
    }
}
