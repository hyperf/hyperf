<?php

namespace Hyperf\Amqp\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\ApplicationContext;
use Hyperf\Framework\Event\BeforeServerStart;

/**
 * @Listener()
 */
class BeforeServerStartListener implements ListenerInterface
{

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BeforeServerStart::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        $container = ApplicationContext::getContainer();

        // Init the consumer process.
        // $consumerManager = $container->get(ConsumerManager::class);
        // $consumerManager->run();

    }

}