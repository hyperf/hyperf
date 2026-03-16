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

namespace Hyperf\Database\SQLite;

use Hyperf\Database\SQLite\Connectors\SQLiteConnector;
use Hyperf\Database\SQLite\Listener\RegisterConnectionListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                'db.connector.sqlite' => SQLiteConnector::class,
            ],
            'listeners' => [
                RegisterConnectionListener::class,
            ],
        ];
    }
}
