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

namespace Hyperf\Retry\Annotation;

use Attribute;
use Hyperf\Retry\BackoffStrategy;

#[Attribute(Attribute::TARGET_METHOD)]
class BackoffRetryFalsy extends RetryFalsy
{
    public function __construct(public int $base = 100, public string $sleepStrategyClass = BackoffStrategy::class)
    {
    }
}
