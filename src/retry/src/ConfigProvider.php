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

use Hyperf\Retry\Aspect\RetryAnnotationAspect;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'aspects' => [
                RetryAnnotationAspect::class,
            ],
            'dependencies' => [
                SleepStrategyInterface::class => FlatStrategy::class,
                RetryBudgetInterface::class => RetryBudget::class,
            ],
        ];
    }
}
