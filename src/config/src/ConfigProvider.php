<?php

namespace Hyperflex\Config;


use Hyperflex\Contract\ConfigInterface;

class ConfigProvider
{

    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ConfigInterface::class => ConfigFactory::class,
            ],
            'scan' => [
                'paths' => [
                    'vendor/hyperflex/config/src'
                ]
            ],
        ];
    }

}