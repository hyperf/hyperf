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
namespace Hyperf\Config\Listener;

use Hyperf\Config\Annotation\Value;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Definition\PropertyHandlerManager;
use Hyperf\Di\ReflectionManager;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

class RegisterPropertyHandlerListener implements ListenerInterface
{
    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event): void
    {
        PropertyHandlerManager::register(Value::class, function ($object, $currentClassName, $targetClassName, $property, $annotation) {
            if ($annotation instanceof Value && ApplicationContext::hasContainer()) {
                $reflectionProperty = ReflectionManager::reflectProperty($currentClassName, $property);
                $reflectionProperty->setAccessible(true);
                $key = $annotation->key;
                $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
                $reflectionProperty->setValue($object, $config->get($key, null));
            }
        });
    }
}
