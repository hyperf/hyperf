<?php

namespace Hyperf\Dispatcher;


class ConfigProvider
{

    public function __invoke(): array
    {
        return [
            'dependencies' => [

            ],
            'scan' => [
                'paths' => [],
            ],
        ];
    }

}