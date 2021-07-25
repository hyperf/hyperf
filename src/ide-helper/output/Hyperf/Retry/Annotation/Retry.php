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

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Retry extends AbstractRetry
{
    public function __construct($policies, $sleepStrategyClass, $maxAttempts, $retryBudget, $base, $retryOnThrowablePredicate, $retryOnResultPredicate, $retryThrowables, $ignoreThrowables, $fallback)
    {
    }
}
