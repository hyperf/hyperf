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

namespace Hyperf\Crontab\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\ReflectionManager;
use Hyperf\Stringable\Str;
use InvalidArgumentException;
use ReflectionMethod;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Crontab extends AbstractAnnotation
{
    public function __construct(
        public ?string $rule = null,
        public ?string $name = null,
        public string $type = 'callback',
        public ?bool $singleton = null,
        public ?string $mutexPool = null,
        public ?int $mutexExpires = null,
        public ?bool $onOneServer = null,
        public null|array|string $callback = null,
        public ?string $memo = null,
        public array|bool|string $enable = true,
        public ?string $timezone = null,
        public array|string $environments = [],
        public array $options = [],
    ) {
        if (! empty($this->rule)) {
            $this->rule = str_replace('\\', '', $this->rule);
        }
    }

    public function collectMethod(string $className, ?string $target): void
    {
        if ($target === null) {
            return;
        }

        if (! $this->name) {
            $this->name = $className . '::' . $target;
        }

        if (! $this->callback) {
            $this->callback = [$className, $target];
        } elseif (is_string($this->callback)) {
            $this->callback = [$className, $this->callback];
        }

        parent::collectMethod($className, $target);
    }

    public function collectClass(string $className): void
    {
        $this->parseName($className);
        $this->parseCallback($className);
        $this->parseEnable($className);

        parent::collectClass($className);
    }

    protected function parseName(string $className): void
    {
        if (! $this->name) {
            $this->name = $className;
        }
    }

    protected function parseCallback(string $className): void
    {
        if (! $this->callback) {
            $reflectionClass = ReflectionManager::reflectClass($className);
            $reflectionMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
            $availableMethodCount = 0;
            $firstAvailableMethod = null;
            $hasInvokeMagicMethod = false;
            foreach ($reflectionMethods as $reflectionMethod) {
                if (! Str::startsWith($reflectionMethod->getName(), ['__'])) {
                    ++$availableMethodCount;
                    ! $firstAvailableMethod && $firstAvailableMethod = $reflectionMethod;
                } elseif ($reflectionMethod->getName() === '__invoke') {
                    $hasInvokeMagicMethod = true;
                }
            }
            if ($availableMethodCount === 1) {
                $this->callback = [$className, $firstAvailableMethod->getName()];
            } elseif ($hasInvokeMagicMethod) {
                $this->callback = [$className, '__invoke'];
            } else {
                throw new InvalidArgumentException('Missing argument $callback of @Crontab annotation.');
            }
        } elseif (is_string($this->callback)) {
            $this->callback = [$className, $this->callback];
        }
    }

    protected function parseEnable(string $className): void
    {
        if ($this->enable === 'true') {
            $this->enable = true;
            return;
        }

        if ($this->enable === 'false') {
            $this->enable = false;
            return;
        }

        if (is_string($this->enable)) {
            $this->enable = [$className, $this->enable];
        }
    }
}
