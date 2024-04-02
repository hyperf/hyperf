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

namespace HyperfTest\ViewEngine\Stub;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Event\EventDispatcher;
use Hyperf\Event\ListenerProvider;
use Hyperf\View\Mode;
use Hyperf\View\Render;
use Hyperf\View\RenderInterface;
use Hyperf\ViewEngine\Component\DynamicComponent;
use Hyperf\ViewEngine\ConfigProvider;
use Hyperf\ViewEngine\HyperfViewEngine;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use ReflectionClass;

class ContainerStub
{
    public static function mockContainer(): ContainerInterface
    {
        $container = new Container(new DefinitionSource(array_merge([
            EventDispatcherInterface::class => EventDispatcher::class,
            ListenerProviderInterface::class => ListenerProvider::class,
        ], (new ConfigProvider())()['dependencies'])));

        ApplicationContext::setContainer($container);

        // register config
        $container->set(ConfigInterface::class, new Config([
            'view' => [
                'engine' => HyperfViewEngine::class,
                'mode' => Mode::SYNC,
                'config' => [
                    'view_path' => __DIR__ . '/../storage/view/',
                    'cache_path' => __DIR__ . '/../storage/cache/',
                ],
                'components' => [
                    'alert' => Alert::class,
                    'alert-slot' => AlertSlot::class,
                    'alert-attribute-merge' => AlertAttributeMerge::class,
                    'alert-attribute-merge-force' => AlertAttributeMergeForce::class,
                    'dynamic-component' => DynamicComponent::class,
                ],
                'namespaces' => [
                    'admin_config' => __DIR__ . '/../admin',
                ],
            ],
        ]));

        $container->set(RenderInterface::class, new Render($container, $container->get(ConfigInterface::class)));

        return $container;
    }

    public static function unsetContainer()
    {
        $ref = new ReflectionClass(ApplicationContext::class);
        $c = $ref->getProperty('container');
        $c->setValue(null);
    }
}
