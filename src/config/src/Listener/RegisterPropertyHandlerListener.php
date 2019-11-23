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

namespace Hyperf\Config\Listener;

use Hyperf\Config\Annotation\Value;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Definition\ObjectDefinition;
use Hyperf\Di\Definition\PropertyHandlerManager;
use Hyperf\Di\Definition\PropertyInjection;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Utils\ApplicationContext;

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
    public function process(object $event)
    {
        PropertyHandlerManager::register(Value::class, function (ObjectDefinition $definition, string $propertyName, $annotation) {
            if ($annotation instanceof Value && ApplicationContext::hasContainer()) {
                $key = $annotation->key;
                $propertyInjection = new PropertyInjection($propertyName, function () use ($key) {
                    $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
                    return $config->get($key, null);
                });
                $definition->addPropertyInjection($propertyInjection);
            }
        });
    }
}
