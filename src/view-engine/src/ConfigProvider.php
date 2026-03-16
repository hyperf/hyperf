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

namespace Hyperf\ViewEngine;

use Hyperf\ViewEngine\Command\GenerateViewCacheCommand;
use Hyperf\ViewEngine\Command\ViewPublishCommand;
use Hyperf\ViewEngine\Compiler\CompilerInterface;
use Hyperf\ViewEngine\Component\DynamicComponent;
use Hyperf\ViewEngine\Contract\EngineResolverInterface;
use Hyperf\ViewEngine\Contract\FactoryInterface;
use Hyperf\ViewEngine\Contract\FinderInterface;
use Hyperf\ViewEngine\Factory\CompilerFactory;
use Hyperf\ViewEngine\Factory\EngineResolverFactory;
use Hyperf\ViewEngine\Factory\FinderFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                FactoryInterface::class => Factory::class,
                EngineResolverInterface::class => EngineResolverFactory::class,
                FinderInterface::class => FinderFactory::class,
                CompilerInterface::class => CompilerFactory::class,
            ],
            'commands' => [
                ViewPublishCommand::class,
                GenerateViewCacheCommand::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                    'collectors' => [
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for view.',
                    'source' => __DIR__ . '/../publish/view.php',
                    'destination' => BASE_PATH . '/config/autoload/view.php',
                ],
            ],
            'view' => [
                'components' => [
                    'dynamic-component' => DynamicComponent::class,
                ],
            ],
        ];
    }
}
