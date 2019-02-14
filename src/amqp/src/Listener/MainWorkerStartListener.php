<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Amqp\Listener;

use Psr\Log\LoggerInterface;
use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Event\Annotation\Listener;
use Psr\Container\ContainerInterface;
use Doctrine\Instantiator\Instantiator;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Amqp\Message\ProducerMessageInterface;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;

/**
 * @Listener
 */
class MainWorkerStartListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ContainerInterface $container, StdoutLoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

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
        // Declare exchange and routingKey
        $producerMessages = AnnotationCollector::getClassByAnnotation(Producer::class);
        if ($producerMessages) {
            $producer = $this->container->get(\Hyperf\Amqp\Producer::class);
            $instantiator = $this->container->get(Instantiator::class);
            foreach ($producerMessages as $producerMessageClass => $messageProperty) {
                $instance = $instantiator->instantiate($producerMessageClass);
                if (! $instance instanceof ProducerMessageInterface) {
                    continue;
                }
                $instance->setExchange($messageProperty['exchange']);
                $instance->setRoutingKey($messageProperty['routingKey']);
                try {
                    $producer->declare($instance);
                    $this->logger->debug(sprintf('AMQP exchange[%s] and routingKey[%s] were created successfully.', $instance->getExchange(), $instance->getRoutingKey()));
                } catch (AMQPProtocolChannelException $e) {
                    $this->logger->debug('AMQPProtocolChannelException: ' . $e->getMessage());
                    // Do nothing.
                }
            }
        }
    }
}
