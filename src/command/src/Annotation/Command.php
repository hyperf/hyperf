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
namespace Hyperf\Command\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS)]
class Command extends AbstractAnnotation
{
    public function __construct(
        public string $name = '',
        public array $arguments = [],
        public array $options = [],
        public string $description = '',
        public array $aliases = [],
        public ?string $signature = null,
    ) {
    }
}
