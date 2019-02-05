<?php

namespace HyperfTest\Event;


use Hyperf\Event\ConfigProvider;
use Hyperf\Event\EventDispatcher;
use Hyperf\Event\ListenerProviderFactory;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class ConfigProviderTest extends TestCase
{
    public function testInvoke()
    {
        $this->assertSame([
            'dependencies' => [
                ListenerProviderInterface::class => ListenerProviderFactory::class,
                EventDispatcherInterface::class => EventDispatcher::class,
            ],
            'scan' => [
                'paths' => [
                    str_replace('/tests', '/src', __DIR__),
                ],
            ],
        ], (new ConfigProvider())());
    }


}