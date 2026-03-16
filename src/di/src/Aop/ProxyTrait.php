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

namespace Hyperf\Di\Aop;

use Closure;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AspectCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\Stdlib\SplPriorityQueue;

trait ProxyTrait
{
    protected static function __proxyCall(
        string $className,
        string $method,
        array $arguments,
        Closure $closure
    ) {
        $proceedingJoinPoint = new ProceedingJoinPoint($closure, $className, $method, $arguments);
        $result = self::handleAround($proceedingJoinPoint);
        unset($proceedingJoinPoint);
        return $result;
    }

    /**
     * @TODO This method will be called everytime, should optimize it later.
     * @deprecated v3.2
     */
    protected static function __getParamsMap(string $className, string $method, array $args): array
    {
        $map = [
            'keys' => [],
            'order' => [],
        ];
        $reflectParameters = ReflectionManager::reflectMethod($className, $method)->getParameters();
        $leftArgCount = count($args);
        foreach ($reflectParameters as $reflectionParameter) {
            $arg = $reflectionParameter->isVariadic() ? $args : array_shift($args);
            if (! isset($arg) && $leftArgCount <= 0) {
                $arg = $reflectionParameter->getDefaultValue();
            }
            --$leftArgCount;
            $map['keys'][$reflectionParameter->getName()] = $arg;
            $map['order'][] = $reflectionParameter->getName();
        }
        return $map;
    }

    protected static function handleAround(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $className = $proceedingJoinPoint->className;
        $methodName = $proceedingJoinPoint->methodName;
        if (! AspectManager::has($className, $methodName)) {
            AspectManager::set($className, $methodName, []);
            $aspects = array_unique(array_merge(static::getClassesAspects($className, $methodName), static::getAnnotationAspects($className, $methodName)));
            $queue = new SplPriorityQueue();
            foreach ($aspects as $aspect) {
                $queue->insert($aspect, AspectCollector::getPriority($aspect));
            }
            while ($queue->valid()) {
                AspectManager::insert($className, $methodName, $queue->current());
                $queue->next();
            }

            unset($aspects, $queue);
        }

        if (empty(AspectManager::get($className, $methodName))) {
            return $proceedingJoinPoint->processOriginalMethod();
        }

        return static::makePipeline()->via('process')
            ->through(AspectManager::get($className, $methodName))
            ->send($proceedingJoinPoint)
            ->then(function (ProceedingJoinPoint $proceedingJoinPoint) {
                return $proceedingJoinPoint->processOriginalMethod();
            });
    }

    protected static function makePipeline(): Pipeline
    {
        $container = ApplicationContext::getContainer();
        if (method_exists($container, 'make')) {
            $pipeline = $container->make(Pipeline::class);
        } else {
            $pipeline = new Pipeline($container);
        }
        return $pipeline;
    }

    protected static function getClassesAspects(string $className, string $method): array
    {
        $aspects = AspectCollector::get('classes', []);
        $matchedAspect = [];
        foreach ($aspects as $aspect => $rules) {
            foreach ($rules as $rule) {
                if (Aspect::isMatch($className, $method, $rule)) {
                    $matchedAspect[] = $aspect;
                    break;
                }
            }
        }
        // The matched aspects maybe have duplicate aspect, should unique it when use it.
        return $matchedAspect;
    }

    protected static function getAnnotationAspects(string $className, string $method): array
    {
        $matchedAspect = [];

        $classAnnotations = AnnotationCollector::get($className . '._c', []);
        $methodAnnotations = AnnotationCollector::get($className . '._m.' . $method, []);
        $annotations = array_unique(array_merge(array_keys($classAnnotations), array_keys($methodAnnotations)));
        if (! $annotations) {
            return $matchedAspect;
        }

        $aspects = AspectCollector::get('annotations', []);
        foreach ($aspects as $aspect => $rules) {
            foreach ($rules as $rule) {
                foreach ($annotations as $annotation) {
                    if (str_contains($rule, '*')) {
                        $preg = str_replace(['*', '\\'], ['.*', '\\\\'], $rule);
                        $pattern = "/^{$preg}$/";
                        if (! preg_match($pattern, $annotation)) {
                            continue;
                        }
                    } elseif ($rule !== $annotation) {
                        continue;
                    }
                    $matchedAspect[] = $aspect;
                }
            }
        }
        // The matched aspects maybe have duplicate aspect, should unique it when use it.
        return $matchedAspect;
    }
}
