<?php

namespace Hyperf\Event;


use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\MessageNotifierInterface;
use Psr\EventDispatcher\TaskProcessorInterface;

class ConfigProvider
{

    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ListenerProviderInterface::class => ListenerProviderFactory::class,
                MessageNotifierInterface::class => MessageNotifier::class,
                TaskProcessorInterface::class => TaskProcessor::class,
            ],
            'scan' => [
                'paths' => [],
            ],
        ];
    }

}