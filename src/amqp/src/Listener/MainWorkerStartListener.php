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
namespace Hyperf\Amqp\Listener;

use Doctrine\Instantiator\Instantiator;
use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

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
            /**
             * @var string $producerMessageClass
             * @var Producer $annotation
             */
            foreach ($producerMessages as $producerMessageClass => $annotation) {
                $instance = $instantiator->instantiate($producerMessageClass);
                if (! $instance instanceof ProducerMessageInterface) {
                    continue;
                }
                $annotation->exchange && $instance->setExchange($annotation->exchange);
                $annotation->routingKey && $instance->setRoutingKey($annotation->routingKey);
                try {
                    $producer->declare($instance, null, true);
                    $routingKey = $instance->getRoutingKey();
                    if (is_array($routingKey)) {
                        $routingKey = implode(',', $routingKey);
                    }
                    $this->logger->debug(sprintf('AMQP exchange[%s] and routingKey[%s] were created successfully.', $instance->getExchange(), $routingKey));
                } catch (AMQPProtocolChannelException $e) {
                    $this->logger->debug('AMQPProtocolChannelException: ' . $e->getMessage());
                    // Do nothing.
                }
            }
        }
    }
}
