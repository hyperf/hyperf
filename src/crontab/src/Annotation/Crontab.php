<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Crontab\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\ReflectionManager;
use Hyperf\Utils\Str;
use ReflectionMethod;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Crontab extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type = 'callback';

    /**
     * @var string
     */
    public $rule;

    /**
     * @var array|string
     */
    public $callback;

    /**
     * @var null|string
     */
    public $memo = '';

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->bindMainProperty('rule', $value);
        $this->rule = str_replace('\\', '', $this->rule);
    }

    public function collectClass(string $className): void
    {
        if (! $this->name) {
            $this->name = $className;
        }
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
                throw new \InvalidArgumentException(sprintf('Missing argument $callback of @Crontab annotation.'));
            }
        } elseif (is_string($this->callback)) {
            $this->callback = [$className, $this->callback];
        }
        parent::collectClass($className);
    }
}
