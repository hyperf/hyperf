<?php

namespace Hyperf\DbConnection;


class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
            'scan' => [
                'paths' => [],
            ],
        ];
    }
}