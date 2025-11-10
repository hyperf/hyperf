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

namespace Hyperf\Etcd;

use Hyperf\Etcd\V3\Auth\EtcdHandlerStackFactory;
use Hyperf\Guzzle\HandlerStackFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                KVInterface::class => KVFactory::class,
                HandlerStackFactory::class => EtcdHandlerStackFactory::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for etcd.',
                    'source' => __DIR__ . '/../publish/etcd.php',
                    'destination' => BASE_PATH . '/config/autoload/etcd.php',
                ],
            ],
        ];
    }
}
