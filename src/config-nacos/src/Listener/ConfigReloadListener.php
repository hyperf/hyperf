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
namespace Hyperf\ConfigNacos\Listener;

use Hyperf\ConfigNacos\Config\ConfigManager;
use Hyperf\ConfigNacos\Config\PipeMessage;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Hyperf\Process\Event\PipeMessage as UserProcessPipMessage;
use Psr\Container\ContainerInterface;

class ConfigReloadListener implements ListenerInterface
{
    /**
     * @var ConfigManager
     */
    protected $manager;

    public function __construct(ContainerInterface $container)
    {
        $this->manager = $container->get(ConfigManager::class);
    }

    public function listen(): array
    {
        return [
            OnPipeMessage::class,
            UserProcessPipMessage::class,
        ];
    }

    public function process(object $event)
    {
        if (property_exists($event, 'data') && $event->data instanceof PipeMessage) {
            $this->manager->merge($event->data->configurations);
        }
    }
}
