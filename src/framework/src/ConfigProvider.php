<?php

namespace Hyperf\Framework;


use Hyperf\Contract\ApplicationInterface;
use Hyperf\Framework\ApplicationFactory;
use Hyperf\Framework\Contract\StdoutLoggerInterface;
use Hyperf\Framework\Logger\StdoutLogger;
use Symfony\Component\Console\Application;

class ConfigProvider
{

    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ApplicationInterface::class => ApplicationFactory::class,
                StdoutLoggerInterface::class => StdoutLogger::class,
            ],
            'scan' => [
                'paths' => []
            ]
        ];
    }

}