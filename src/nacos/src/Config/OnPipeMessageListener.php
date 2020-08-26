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
namespace Hyperf\Nacos\Config;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Hyperf\Nacos\Constants;
use Hyperf\Process\Event\PipeMessage as UserProcessPipMessage;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;

class OnPipeMessageListener implements ListenerInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

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
            UserProcessPipMessage::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        if (property_exists($event, 'data') && $event->data instanceof PipeMessage) {
            $root = $this->config->get('nacos.config_append_node');
            foreach ($event->data->configurations ?? [] as $key => $conf) {
                $configKey = $root ? $root . '.' . $key : $key;
                if (is_array($conf) && $this->config->get('nacos.config_merge_mode') == Constants::CONFIG_MERGE_APPEND) {
                    $conf = Arr::merge($this->config->get($configKey, []), $conf);
                }
                $this->config->set($configKey, $conf);
            }
        }
    }
}
