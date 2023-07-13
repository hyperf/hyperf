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
use Hyperf\Di\Annotation\AbstractMultipleAnnotation;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class AsCommand extends AbstractMultipleAnnotation
{
    public function __construct(
        public string $signature = '',
        public string $description = '',
        public string $handle = 'handle',
        public array $aliases = [],
    ) {
    }

    public function collectMethod(string $className, ?string $target): void
    {
        AsCommandCollector::set(
            sprintf('%s@%s:%s', $className, $target, crc32($this->signature)),
            [
                'class' => $className,
                'method' => $target,
                'signature' => $this->signature,
                'description' => $this->description,
                'aliases' => $this->aliases,
            ]
        );
    }

    public function collectClass(string $className): void
    {
        $target = $this->handle ?: 'handle';

        $this->collectMethod($className, $target);
    }
}
