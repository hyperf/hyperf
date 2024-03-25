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

namespace Hyperf\Server;

use Hyperf\Server\Command\StartServer;
use Hyperf\Server\Listener\AfterWorkerStartListener;
use Hyperf\Server\Listener\InitProcessTitleListener;
use Hyperf\Server\Listener\StoreServerNameListener;
use Swoole\Server as SwooleServer;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                SwooleServer::class => SwooleServerFactory::class,
            ],
            'listeners' => [
                StoreServerNameListener::class,
                AfterWorkerStartListener::class,
                InitProcessTitleListener::class,
            ],
            'commands' => [
                StartServer::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for server.',
                    'source' => __DIR__ . '/../publish/server.php',
                    'destination' => BASE_PATH . '/config/autoload/server.php',
                ],
            ],
        ];
    }
}
