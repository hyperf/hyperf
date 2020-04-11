<?php

declare(strict_types = 1);

namespace Hyperf\Nacos\Config;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Psr\Container\ContainerInterface;

class OnPipeMessageListener implements ListenerInterface
{
    /**
     * @var \Hyperf\Config\Config
     */
    private $config;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            OnPipeMessage::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        if (property_exists($event, 'data') && $event->data instanceof PipeMessage) {
            $conf_arr = $event->data->configurations ?? [];

            $append_node = config('nacos.configAppendNode');
            foreach ($conf_arr as $key => $conf) {
                $this->config->set($append_node ? $append_node . '.' . $key : $key, $conf);
            }
        }
    }
}
