<?php

namespace Hyperf\Amqp\Listener;

use Doctrine\Instantiator\Instantiator;
use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\ApplicationContext;
use Hyperf\Framework\Contract\StdoutLoggerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;

/**
 * @Listener()
 */
class MainWorkerStartListener implements ListenerInterface
{

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            MainWorkerStart::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        $container = ApplicationContext::getContainer();
        $stdoutLogger = $container->get(StdoutLoggerInterface::class);

        // Declare exchange and routingKey
        $producerMessages = AnnotationCollector::getClassByAnnotation(Producer::class);
        if ($producerMessages) {
            $producer = $container->get(\Hyperf\Amqp\Producer::class);
            $instantiator = $container->get(Instantiator::class);
            foreach ($producerMessages as $producerMessageClass => $messageProperty) {
                $instance = $instantiator->instantiate($producerMessageClass);
                if (! $instance instanceof ProducerMessageInterface) {
                    continue;
                }
                $instance->setExchange($messageProperty['exchange']);
                $instance->setRoutingKey($messageProperty['routingKey']);
                try {
                    $producer->declare($instance);
                    $stdoutLogger->debug(sprintf('AMQP Exchange[%s] and RoutingKey[%s] create successful.', $instance->getExchange(), $instance->getRoutingKey()));
                } catch (AMQPProtocolChannelException $e) {
                    $stdoutLogger->debug('AMQPProtocolChannelException: ' . $e->getMessage());
                    // Do nothing.
                }
            }
        }
    }

}