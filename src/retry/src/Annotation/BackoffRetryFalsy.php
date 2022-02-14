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
use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\Retry\BackoffStrategy;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class BackoffRetryFalsy extends RetryFalsy
{
    public $base = 100;

    public $sleepStrategyClass = BackoffStrategy::class;
}
