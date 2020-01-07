<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\RpcClient;

use Hyperf\Rpc\IdGenerator\IdGeneratorInterface;
use Hyperf\Rpc\IdGenerator\UniqidIdGenerator;
use Hyperf\RpcClient\Listener\AddConsumerDefinitionListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                AddConsumerDefinitionListener::class,
            ],
            'dependencies' => [
                IdGeneratorInterface::class => UniqidIdGenerator::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
        ];
    }
}
