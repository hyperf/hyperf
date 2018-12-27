<?php

namespace Hyperf\Config;


use Hyperf\Contract\ConfigInterface;

class ConfigProvider
{

    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ConfigInterface::class => ConfigFactory::class,
            ],
            'scan' => [
                'paths' => []
            ],
        ];
    }

}