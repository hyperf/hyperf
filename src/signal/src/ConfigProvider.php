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

namespace Hyperf\Signal;

use Hyperf\Signal\Listener\SignalDeregisterListener;
use Hyperf\Signal\Listener\SignalRegisterListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                SignalRegisterListener::class => PHP_INT_MAX,
                SignalDeregisterListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for signal.',
                    'source' => __DIR__ . '/../publish/signal.php',
                    'destination' => BASE_PATH . '/config/autoload/signal.php',
                ],
            ],
        ];
    }
}
