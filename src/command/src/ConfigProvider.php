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
namespace Hyperf\Command;

use Hyperf\Command\Annotation\CommandCollector;
use Hyperf\Command\Listener\RegisterCommandListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'annotations' => [
                'scan' => [
                    'collectors' => [
                        CommandCollector::class,
                    ],
                ],
            ],
            'listeners' => [
                RegisterCommandListener::class,
            ],
        ];
    }
}
