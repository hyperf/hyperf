<?php

namespace Hyperflex\Framework;


use Hyperflex\Framework\ApplicationFactory;
use Hyperflex\Framework\Contract\StdoutLoggerInterface;
use Hyperflex\Framework\Logger\StdoutLogger;
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
            'scan'=>[
                'paths'=>[
                    'vendor/hyperflex/framework/src'
                ]
            ]
        ];
    }

}