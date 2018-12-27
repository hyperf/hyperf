<?php

namespace Hyperf\Memory;


class ConfigProvider
{

    public function __invoke(): array
    {
        return [
            'dependencies' => [],
            'scan' => [
                'paths' => [],
            ]
        ];
    }

}