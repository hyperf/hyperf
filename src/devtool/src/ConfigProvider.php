<?php

namespace Hyperf\Devtool;

use Hyperf\Devtool\Command\ProxyCreateCommand;
use Hyperf\Devtool\Command\Factory\ProxyCreateCommandFactory;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                ProxyCreateCommand::class => ProxyCreateCommandFactory::class,
            ],
            'commands' => [
                ProxyCreateCommand::class,
            ],
            'scan' => [
                'paths' => [],
            ],
        ];
    }
}