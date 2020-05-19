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
namespace Hyperf\Di\Aop;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Definition\PropertyHandlerManager;
use Hyperf\Di\ReflectionManager;
use Hyperf\Utils\ApplicationContext;

class RegisterInjectPropertyHandler
{
    /**
     * Even the Inject has been handled by constructor of proxy class, but the Aspect class does not works,
     * So inject the value one more time here.
     */
    public static function register()
    {
        PropertyHandlerManager::register(Inject::class, function ($object, $currentClassName, $targetClassName, $property, $annotation) {
            if ($annotation instanceof Inject) {
                try {
                    $reflectionProperty = ReflectionManager::reflectProperty($currentClassName, $property);
                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($object, ApplicationContext::getContainer()->get($annotation->value));
                } catch (\Throwable $throwable) {
                    if ($annotation->required) {
                        throw $throwable;
                    }
                }
            }
        });
    }
}
