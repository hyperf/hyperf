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

namespace Hyperf\Clickhouse;

use ClickHouseDB\Client;
use Hyperf\Clickhouse\Exception\NotHookException;
use Hyperf\Contract\ConfigInterface;

class ClickhouseFactory
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function create($pool = 'default'): Client
    {
        $config = $this->config->get('clickhouse.' . $pool, []);
        $settings = $config['settings'] ?? [];

        // if ((swoole_hook_flags() & SWOOLE_HOOK_CURL) !== SWOOLE_HOOK_CURL) {
        //     throw new NotHookException('The swoole hook flags not support CURL.');
        // }

        return new Client($config, $settings);
    }
}
