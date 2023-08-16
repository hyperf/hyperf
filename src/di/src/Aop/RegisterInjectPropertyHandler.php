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

use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Definition\PropertyHandlerManager;
use Hyperf\Di\Exception\NotFoundException;
use Hyperf\Di\ReflectionManager;
use Throwable;

class RegisterInjectPropertyHandler
{
    public static bool $registered = false;

    /**
     * Even the Inject has been handled by constructor of proxy class, but the Aspect class does not work,
     * So inject the value one more time here.
     */
    public static function register()
    {
        if (static::$registered) {
            return;
        }
        PropertyHandlerManager::register(Inject::class, function ($object, $currentClassName, $targetClassName, $property, $annotation) {
            if ($annotation instanceof Inject) {
                try {
                    $reflectionProperty = ReflectionManager::reflectProperty($currentClassName, $property);
                    $reflectionProperty->setAccessible(true);
                    $container = ApplicationContext::getContainer();
                    if ($container->has($annotation->value)) {
                        $reflectionProperty->setValue($object, $container->get($annotation->value));
                    } elseif ($annotation->required) {
                        throw new NotFoundException("No entry or class found for '{$annotation->value}'");
                    }
                } catch (Throwable $throwable) {
                    if ($annotation->required) {
                        throw $throwable;
                    }
                }
            }
        });

        static::$registered = true;
    }
}
