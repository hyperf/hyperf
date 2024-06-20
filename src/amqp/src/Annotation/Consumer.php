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

namespace Hyperf\Amqp\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS)]
class Consumer extends AbstractAnnotation
{
    public function __construct(
        public string $exchange = '',
        public array|string $routingKey = '',
        public string $queue = '',
        public string $name = 'Consumer',
        public ?int $nums = null,
        public ?bool $enable = null,
        public int $maxConsumption = 0,
        public ?string $pool = null
    ) {
    }
}
