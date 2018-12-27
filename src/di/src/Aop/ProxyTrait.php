<?php


namespace Hyperflex\Di\Aop;


use Closure;
use Doctrine\Instantiator\Instantiator;
use Hyperflex\Di\Annotation\AspectCollector;
use Hyperflex\Di\ReflectionManager;
use Hyperflex\Framework\Hyperflex;

trait ProxyTrait
{

    protected static function __proxyCall(
        string $originalClassName,
        string $method,
        array $arguments,
        Closure $closure
    ) {
        echo $originalClassName . '::' . $method . '.pre' . PHP_EOL;
        $proceedingJoinPoint = new ProceedingJoinPoint($closure, $originalClassName, $method, $arguments);
        $result = self::handleArround($proceedingJoinPoint);
        echo $originalClassName . '::' . $method . '.post' . PHP_EOL;
        unset($proceedingJoinPoint);
        return $result;
    }

    /**
     * @TODO This method will be called everytime, should optimize it later.
     */
    protected static function getParamsMap(string $className, string $method, array $args): array
    {
        $map = [
            'keys' => [],
            'order' => [],
        ];
        $reflectMethod = ReflectionManager::reflectMethod($className, $method);
        $reflectParameters = $reflectMethod->getParameters();
        foreach ($reflectParameters as $key => $reflectionParameter) {
            if (! isset($args[$key])) {
                $args[$key] = $reflectionParameter->getDefaultValue();
            }
            $map['keys'][$reflectionParameter->getName()] = $args[$key];
            $map['order'][] = $reflectionParameter->getName();
        }
        return $map;
    }

    private static function handleArround(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $arround = AspectCollector::get('arround');
        if ($aspects = self::isMatchClassName($arround['classes'] ?? [], $proceedingJoinPoint->className, $proceedingJoinPoint->method)) {
            $pipeline = new Pipeline(Hyperflex::getContainer());
            return $pipeline->via('process')->through($aspects)->send($proceedingJoinPoint)->then(function (ProceedingJoinPoint $proceedingJoinPoint) {
                return $proceedingJoinPoint->processOriginalMethod();
            });
        } else {
            return $proceedingJoinPoint->processOriginalMethod();
        }
    }

    private static function isMatchClassName(array $aspects, string $className, string $method)
    {
        // @TODO Handle wildcard character
        $matchAspect = [];
        foreach ($aspects as $aspect => $item) {
            foreach ($item as $class) {
                if (strpos($class, '::') !== false) {
                    [$expectedClass, $expectedMethod] = explode('::', $class);
                    if ($expectedClass === $className && $expectedMethod === $method) {
                        $matchAspect[] = $aspect;
                        break;
                    }
                } else {
                    if ($class === $className) {
                        $matchAspect[] = $aspect;
                        break;
                    }
                }
            }
        }
        return $matchAspect;
    }

}