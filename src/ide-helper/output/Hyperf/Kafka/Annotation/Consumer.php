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
namespace Hyperf\Kafka\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS)]
class Consumer extends AbstractAnnotation
{
    public function __construct(string $pool, string|array $topic, ?string $groupId, ?string $memberId, bool $autoCommit = true, int $nums = 1, bool $enable = true)
    {
    }
}
