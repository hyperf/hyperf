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

namespace Hyperf\Session;

use Hyperf\Contract\SessionInterface;
use Hyperf\Session\Handler\DatabaseHandler;
use Hyperf\Session\Handler\DatabaseHandlerFactory;
use Hyperf\Session\Handler\FileHandler;
use Hyperf\Session\Handler\FileHandlerFactory;
use Hyperf\Session\Handler\RedisHandler;
use Hyperf\Session\Handler\RedisHandlerFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                FileHandler::class => FileHandlerFactory::class,
                DatabaseHandler::class => DatabaseHandlerFactory::class,
                RedisHandler::class => RedisHandlerFactory::class,
                SessionInterface::class => SessionProxy::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of session.',
                    'source' => __DIR__ . '/../publish/session.php',
                    'destination' => BASE_PATH . '/config/autoload/session.php',
                ],
            ],
        ];
    }
}
