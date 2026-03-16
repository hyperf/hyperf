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

namespace Hyperf\ServiceGovernance;

use Hyperf\Contract\IPReaderInterface;
use Hyperf\ServiceGovernance\Listener\RegisterServiceListener;
use Hyperf\Support\IPReader;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                IPReaderInterface::class => IPReader::class,
            ],
            'listeners' => [
                RegisterServiceListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of service governance.',
                    'source' => __DIR__ . '/../publish/services.php',
                    'destination' => BASE_PATH . '/config/autoload/services.php',
                ],
            ],
        ];
    }
}
